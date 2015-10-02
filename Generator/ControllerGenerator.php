<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * ControllerGenerator
 *
 * @package GTheron\RestBundle\Generator;
 * @author Gabriel Théron
*/
class ControllerGenerator extends Generator
{
    private $filesystem;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, $controller, $routeFormat, array $actions = array())
    {
        $dir = $bundle->getPath();
        $controllerFile = $dir.'/Controller/'.$controller.'Controller.php';
        if (file_exists($controllerFile)) {
            throw new \RuntimeException(sprintf('Controller "%s" already exists', $controller));
        }

        $parameters = array(
            'namespace'  => $bundle->getNamespace(),
            'bundle'     => $bundle->getName(),
            'format'     => array(
                'routing'    => $routeFormat,
            ),
            'controller' => $controller,
        );

        $this->generateRouting($bundle, $controller, $routeFormat);

        foreach ($actions as $i => $action) {
            // get the actioname without the sufix Action (for the template logical name)
            $actions[$i]['basename'] = substr($action['name'], 0, -6);
            $params = $parameters;
            $params['action'] = $actions[$i];

            //$this->generateRouting($bundle, $controller, $actions[$i], $routeFormat);
        }

        $parameters['actions'] = $actions;

        $this->renderFile('controller/Controller.php.twig', $controllerFile, $parameters);
        $this->renderFile('controller/ControllerTest.php.twig', $dir.'/Tests/Controller/'.$controller.'ControllerTest.php', $parameters);
    }

    public function generateRouting(BundleInterface $bundle, $controller, $format)
    {
        // annotation is generated in the templates
        if ('annotation' == $format) {
            return true;
        }

        $file = $bundle->getPath().'/Resources/config/routing.'.$format;
        if (file_exists($file)) {
            $content = file_get_contents($file);
        } elseif (!is_dir($dir = $bundle->getPath().'/Resources/config')) {
            mkdir($dir);
        }

        $name = strtolower(preg_replace('/([A-Z])/', '_\\1', $bundle->getName().'_'.$controller));
        //TODO get prefix from parameters
        $prefix = '/api/';

        $resource = $bundle->getNamespace()."\\Controller\\".$controller."Controller";

        //$controller = $bundle->getName().':'.$controller.':'.$action['basename'];
        //$name = strtolower(preg_replace('/([A-Z])/', '_\\1', $action['basename']));

        if ('yml' == $format) {
            // yaml
            if (!isset($content)) {
                $content = '';
            }

            $content .= sprintf(
                "\n%s:\n    type: rest\n    prefix: %s\n    resource: %s }\n",
                $name,
                $prefix,
                $resource
            );
        } elseif ('xml' == $format) {
            throw new \Exception('XML route format not yet supported');
            // xml
            if (!isset($content)) {
                // new file
                $content = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://symfony.com/schema/routing"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">
</routes>
EOT;
            }

            $sxe = simplexml_load_string($content);

            $route = $sxe->addChild('route');
            $route->addAttribute('id', $name);
            $route->addAttribute('path', $action['route']);

            $default = $route->addChild('default', $controller);
            $default->addAttribute('key', '_controller');

            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($sxe->asXML());
            $content = $dom->saveXML();
        } elseif ('php' == $format) {
            throw new \Exception('PHP route format not yet supported');
            // php
            if (isset($content)) {
                // edit current file
                $pointer = strpos($content, 'return');
                if (!preg_match('/(\$[^ ]*).*?new RouteCollection\(\)/', $content, $collection) || false === $pointer) {
                    throw new \RunTimeException('Routing.php file is not correct, please initialize RouteCollection.');
                }

                $content = substr($content, 0, $pointer);
                $content .= sprintf("%s->add('%s', new Route('%s', array(", $collection[1], $name, $action['route']);
                $content .= sprintf("\n    '_controller' => '%s',", $controller);
                $content .= "\n)));\n\nreturn ".$collection[1].";";
            } else {
                // new file
                $content = <<<EOT
<?php
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

\$collection = new RouteCollection();
EOT;
                $content .= sprintf("\n\$collection->add('%s', new Route('%s', array(", $name, $action['route']);
                $content .= sprintf("\n    '_controller' => '%s',", $controller);
                $content .= "\n)));\n\nreturn \$collection;";
            }
        }

        $flink = fopen($file, 'w');
        if ($flink) {
            $write = fwrite($flink, $content);

            if ($write) {
                fclose($flink);
            } else {
                throw new \RunTimeException(sprintf('We cannot write into file "%s", has that file the correct access level?', $file));
            }
        } else {
            throw new \RunTimeException(sprintf('Problems with generating file "%s", did you gave write access to that directory?', $file));
        }
    }
}