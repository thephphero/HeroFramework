<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 28/10/17
 * Time: 19:28
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Console;


use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
class Application extends ConsoleApplication{

    private $kernel;
    private $commandsRegistered = false;

    private $registrationErrors = [];

    public function __construct(HttpKernelInterface $kernel,$name = 'HeroFramework', $version = 'UNKNOWN')
    {
        $this->kernel=$kernel;

        parent::__construct($name, Kernel::VERSION.' - '.$kernel->getName().'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));

        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
    }


    public function getKernel()
    {
        return $this->kernel;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        foreach ($this->all() as $command) {
            if ($command instanceof ContainerAwareInterface) {
                $command->setContainer($container);
            }
        }
        $this->setDispatcher($container->get('event_dispatcher'));

        return parent::doRun($input, $output);
    }

    public function add(Command $command)
    {
        $this->registerCommands();
        return parent::add($command);
    }

    /**
     * {@inheritdoc}
     */
    public function find($name)
    {
        $this->registerCommands();
        return parent::find($name);
    }
    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $this->registerCommands();
        $command = parent::get($name);
        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->kernel->getContainer());
        }
        return $command;
    }
    /**
     * {@inheritdoc}
     */
    public function all($namespace = null)
    {
        $this->registerCommands();
        return parent::all($namespace);
    }


    protected function registerCommands()
    {

        if ($this->commandsRegistered) {
            return;
        }
        $this->commandsRegistered = true;

        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        if ($container->hasParameter('console.command.ids')) {
            $lazyCommandIds = $container->hasParameter('console.lazy_command.ids') ? $container->getParameter('console.lazy_command.ids') : [];
            foreach ($container->getParameter('console.command.ids') as $id) {
                if (!isset($lazyCommandIds[$id])) {
                    try {
                        $this->add($container->get($id));
                    } catch (\Throwable $e) {
                        $this->registrationErrors[] = $e;
                    }
                }
            }
        }
    }
}
