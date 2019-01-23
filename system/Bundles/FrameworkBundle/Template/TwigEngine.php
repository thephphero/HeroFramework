<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 03/01/19
 * Time: 16:42
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Template;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\StreamingEngineInterface;
use Twig\Environment;
use Twig\Parser;

class TwigEngine implements EngineInterface, StreamingEngineInterface{

    protected $environment;

    protected $parser;

    public function __construct(Environment $environment, Parser $parser)
    {
        $this->environment = $environment;

        $this->parser = $parser;
    }

    public function exists($name)
    {
        if($name instanceof \Twig\Template){
            return true;
        }

        return $this->environment->getLoader()->exists($name);
    }

    public function render($name, array $parameters = array())
    {
        return $this->environment->render($name,$parameters);
    }

    public function stream($name, array $parameters = array())
    {
        // TODO: Implement stream() method.
    }

    public function supports($name)
    {
        if ($name instanceof Template) {
            return true;
        }
        $template = $this->parser->parse($name);
        return 'twig' === $template->get('engine');
    }
}