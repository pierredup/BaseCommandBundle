<?php
namespace Afrihost\BaseCommandBundle\Helper\Logging;

use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Helper\AbstractEnhancement;
use Afrihost\BaseCommandBundle\Helper\Logging\Handler\ConsoleHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoggingEnhancement extends AbstractEnhancement
{
    /**
     * @var Logger
     */
    private $logger = null;

    private $preInitQueue = array();

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        // The logger is always going to be available, whether we have handlers or not:
        $this->logger = new Logger($this->getUserCommandClassFilename());

        $this->initializeFileLogger($input, $output);
        $this->initializeConsoleLogger($input, $output);

        if(!empty($this->preInitQueue)){
            foreach($this->preInitQueue  as $logEntry){
                $this->getLogger()->addRecord($logEntry['level'], $logEntry['message'], $logEntry['context']);
            }
            $this->preInitQueue = array();
        }
    }

    protected function initializeFileLogger(InputInterface $input, OutputInterface $output)
    {
        if ($this->getRuntimeConfig()->isLogToFile()){

            // Generate a logFilename based on the name of the user's Command if one has not yet been explicitly specified
            $logFilename = $this->getRuntimeConfig()->getLogFilename(false);
            if (is_null($logFilename)) {
                $logFilename = $this->getUserCommandClassFilename().$this->getRuntimeConfig()->getDefaultLogFileExtension();
                $this->getRuntimeConfig()->setLogFilename($logFilename);
            }

            $fileHandler = new StreamHandler($this->getRuntimeConfig()->getLogFilename(true), $this->getRuntimeConfig()->getLogLevel());
            $formatter = new LineFormatter(
                $this->getRuntimeConfig()->getFileLogLineFormat(),
                null,
                $this->getRuntimeConfig()->getFileLogLineBreaks()
            );
            $fileHandler->setFormatter($formatter);
            $this->logger->pushHandler($fileHandler);
        }

        return $this;
    }

    protected function initializeConsoleLogger(InputInterface $input, OutputInterface $output){
        if ($this->getRuntimeConfig()->isLogToConsole()) {

            $consoleHandler = new ConsoleHandler($output, $this->getRuntimeConfig()->getLogLevel());
            $formatter = new LineFormatter(
                $this->getRuntimeConfig()->getConsoleLogLineFormat(),
                null,
                $this->getRuntimeConfig()->getConsoleLogLineBreaks()
            );
            $consoleHandler->setFormatter($formatter);
            $this->logger->pushHandler($consoleHandler);

        }
    }

    /**
     * Logic that needs to be hooked in before the command's run() function is invoked (i.e. after construction but before
     * initialization) should be placed here.  The function will be called by the BaseCommand's preRun() function
     *
     * @param OutputInterface $output
     */
    public function preRun(OutputInterface $output)
    {
        // Nothing at the moment
    }

    /**
     * Cleanup logic that is to be executed after the command has been run should be implemented here. This function will
     * be called BaseCommand's postRun() function
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param int|null        $exitCode
     */
    public function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        // Nothing at the moment
    }

    /**
     * Provides access to the logger object while maintaining its encapsulation so that all initialisation logic is done
     * in this class
     *
     * @return Logger
     * @throws BaseCommandException
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            throw new BaseCommandException('Cannot access logger. It is not yet initialised.');
        }

        return $this->logger;
    }

    /**
     * There are cases where log messages are generated prior to the the log handler being initialized. This function
     * allows such messages to be queued up. The queue is then automatically flushed straight after the log handlers are
     * configured
     *
     * @param int    $logLevel The Monolog logging level
     * @param string $message  The log message
     * @param array  $context  The log context
     *
     * @return LoggingEnhancement
     */
    public function pushLogMessageOnPreInitQueue($logLevel, $message, array $context = array())
    {
        $this->preInitQueue[] = array('level'=>$logLevel, 'message'=>$message, 'context'=>$context);
        return $this;
    }
}