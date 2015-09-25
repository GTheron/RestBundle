<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Command;

use GTheron\RestBundle\Generator\ControllerGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * GenerateControllerCommand
 * Help inspired by GeneratorBundle\GenerateControllerCommand
 *
 * @package GTheron\RestBundle\Command;
 * @author Gabriel Théron
*/
class GenerateControllerCommand extends GeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:resource-controller')
            ->setDescription('Generates a rest controller for a Resource')
            ->setDefinition(array(
                new InputOption('resource-name', '', InputOption::VALUE_REQUIRED, 'The name of the Resource this controller exposes'),
                new InputOption('route-format', '', InputOption::VALUE_REQUIRED, 'The format that is used for the routing (yml, xml, php, annotation)', 'annotation'),
                new InputOption('actions', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The actions in the controller'),
            ))
            ->setHelp(<<<EOT
The <info>generate:resource-controller</info> command helps you generates new controllers for resources
inside bundles.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--bundle</comment> and <comment>--resource-name</comment> are the only
ones needed if you follow the conventions):

<info>php app/console generate:resource-controller --resource-name=AcmeBlogBundle:Post</info>

If you want to disable any user interaction, use <comment>--no-interaction</comment>
but don't forget to pass all needed options:

<info>php app/console generate:resource-controller --resource-name=AcmeBlogBundle:Post --no-interaction</info>
EOT
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new Question($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        if (null === $input->getOption('resource-name')) {
            throw new \RuntimeException('The resource-name option must be provided.');
        }

        list($bundle, $controller) = $this->parseShortcutNotation($input->getOption('resource-name'));
        if (is_string($bundle)) {
            $bundle = Validators::validateBundleName($bundle);

            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }

        $questionHelper->writeSection($output, 'Controller generation');

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $controller, $input->getOption('route-format'), $this->parseActions($input->getOption('actions')));

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $questionHelper->writeGeneratorSummary($output, array());
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the GTheron ResourceController generator');

        // namespace
        $output->writeln(array(
            '',
            'In a rest app, resources need to be exposed by at least one <comment>resource controller</comment>.',
            'This command helps you generate them easily.',
            '',
            'First, you need to give the class of the resource this controller will operate on.',
            'You must use the entity\' shortname like so: <comment>AcmeBlogBundle:Post</comment>',
            '',
        ));

        while (true) {
            $question = new Question($questionHelper->getQuestion('Resource class', $input->getOption('resource-name')), $input->getOption('resource-name'));
            //TODO add validator for class finding
            //$question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateController'));
            $controller = $questionHelper->ask($input, $output, $question);
            list($bundle, $controller) = $this->parseShortcutNotation($controller);

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);

                if (!file_exists($b->getPath().'/Controller/'.$controller.'Controller.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Controller "%s:%s" already exists.</>', $bundle, $controller));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }
        $input->setOption('resource-name', $bundle.':'.$controller);

        // routing format
        $defaultFormat = (null !== $input->getOption('route-format') ? $input->getOption('route-format') : 'annotation');
        $output->writeln(array(
            '',
            'Determine the format to use for the routing.',
            '',
        ));
        $question = new Question($questionHelper->getQuestion('Routing format (php, xml, yml, annotation)', $defaultFormat), $defaultFormat);
        $question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'));
        $routeFormat = $questionHelper->ask($input, $output, $question);
        $input->setOption('route-format', $routeFormat);

        // actions
        $input->setOption('actions', $this->addActions($input, $output, $questionHelper));

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg-white', true),
            '',
            sprintf('You are going to generate a "<info>%s:%s</info>" controller', $bundle, $controller),
            sprintf('using the "<info>%s</info>" format for the routing', $routeFormat)
        ));
    }

    public function addActions(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $output->writeln(array(
            '',
            'Instead of starting with a blank controller, you can add some actions now. An action',
            'is a PHP function or method that executes, for example, when a given route is matched.',
            'Actions should be suffixed by <comment>Action</comment>.',
            '',
        ));

        $actions = $this->parseActions($input->getOption('actions'));

        while (true) {
            // name
            $output->writeln('');
            $question = new Question($questionHelper->getQuestion('New action name (press <return> to stop adding actions)', null), null);
            $question->setValidator(function ($name) use ($actions) {
                if (null == $name) {
                    return $name;
                }

                if (isset($actions[$name])) {
                    throw new \InvalidArgumentException(sprintf('Action "%s" is already defined', $name));
                }

                if ('Action' != substr($name, -6)) {
                    throw new \InvalidArgumentException(sprintf('Name "%s" is not suffixed by Action', $name));
                }

                return $name;
            });

            $actionName = $questionHelper->ask($input, $output, $question);
            if (!$actionName) {
                break;
            }

            // route
            $question = new Question($questionHelper->getQuestion('Action route', '/'.substr($actionName, 0, -6)), '/'.substr($actionName, 0, -6));
            $route = $questionHelper->ask($input, $output, $question);
            $placeholders = $this->getPlaceholdersFromRoute($route);

            // adding action
            $actions[$actionName] = array(
                'name'         => $actionName,
                'route'        => $route,
                'placeholders' => $placeholders,
            );
        }

        return $actions;
    }

    public function parseActions($actions)
    {
        if (is_array($actions)) {
            return $actions;
        }

        $newActions = array();

        foreach (explode(' ', $actions) as $action) {
            $data = explode(':', $action);

            // name
            if (!isset($data[0])) {
                throw new \InvalidArgumentException('An action must have a name');
            }
            $name = array_shift($data);

            // route
            $route = (isset($data[0]) && '' != $data[0]) ? array_shift($data) : '/'.substr($name, 0, -6);
            if ($route) {
                $placeholders = $this->getPlaceholdersFromRoute($route);
            } else {
                $placeholders = array();
            }
        }

        return $newActions;
    }

    public function getPlaceholdersFromRoute($route)
    {
        preg_match_all('/{(.*?)}/', $route, $placeholders);
        $placeholders = $placeholders[1];

        return $placeholders;
    }

    /**
     * @param string $name
     * @return array
     */
    public function parseShortcutNotation($name)
    {
        $entity = str_replace('/', '\\', $name);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The controller name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    /**
     * @return ControllerGenerator
     */
    protected function createGenerator()
    {
        return new ControllerGenerator($this->getContainer()->get('filesystem'));
    }
}