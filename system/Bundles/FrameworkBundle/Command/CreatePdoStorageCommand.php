<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 14/08/19
 * Time: 17:07
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Command;

use Bundles\FrameworkBundle\Log\Log;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class CreatePdoStorageCommand extends Command{

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:session';

    protected $logger;

    protected $sessionHandler;

    public function __construct(PdoSessionHandler $pdoSessionHandler , Log $log=null)
    {
        $this->logger = $log;

        $this->sessionHandler = $pdoSessionHandler;

        parent::__construct();
    }

    public function configure(){
        $this->setDescription('Initializes a database table for storing sessions.')
            ->setHelp('This command allows you to create a database table for storing sessions.');

        $this->addArgument('table', InputArgument::OPTIONAL,'Name of the sessions table. Default: sessions' );


    }

    public function execute(InputInterface $input, OutputInterface $output){

        $options = [];

        $output->writeln([
            'PDO Session Storage Creator',
            '===========================',
            '',
        ]);

        // retrieve the argument value using getArgument()
        $output->writeln('Table name (default: sessions): '.$input->getArgument('table'));

        if($input->hasArgument('table')){
            $options['db_table'] = $input->getArgument('table');
        }

        try{

            $this->sessionHandler->createTable();
            $output->writeln('Table successfully generated!');

        }catch (Exception $exception){
            $this->logger->log(LOG_ERR,$exception->getMessage());
            $output->writeln('There was a problem while trying to generate your table. Please check the logs.');
        }

    }

}