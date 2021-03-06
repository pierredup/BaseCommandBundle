<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IconCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('test:icon')
            ->setDescription('This command displays icons')
            ->addOption('icon', null, InputOption::VALUE_OPTIONAL, 'Icon', 'tick')
            ->addOption('colour', null, InputOption::VALUE_REQUIRED, 'Foreground colour')
            ->addOption('bgcolour', null, InputOption::VALUE_REQUIRED, 'Background colour')
            ->addOption('style', null, InputOption::VALUE_REQUIRED, 'Style');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iconMethod = $input->getOption('icon');
        $colour = $input->getOption('colour');
        $bgColour = 'bg' . ucfirst(strtolower($input->getOption('bgcolour')));
        $style = $input->getOption('style');

        $icon = $this->getIcon()->$iconMethod();

        if(!empty($colour)){
            $icon = $icon->$colour();
        }

        if(!empty($bgColour)){
            $icon = $icon->$bgColour();
        }

        if(!empty($style)){
            $icon = $icon->$style();
        }

        $icon = $icon->render();

        $output->writeln($icon);
    }
}