<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Finder\Finder;

class ListPharContentCommand extends Command
{

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		// @todo enable excluding of files
		$this->setName('phar:list')
			 ->setDescription('Lists the content of a given phar')
			 ->setDefinition(array(
				new InputOption('tree', 't', InputOption::VALUE_NONE, 'Lists the content as tree'), // Not implemented yet
				new InputOption('search', 's', InputOption::VALUE_REQUIRED, 'Searches a specific string'),
				new InputArgument('path', InputArgument::REQUIRED, 'The path of the phar')
			 ));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $input->getArgument('path');

		$finder = new Finder();
		$finder->removeAdapters();
		$finder->addAdapter(new \Symfony\Component\Finder\Adapter\PhpAdapter());

		$finder->files()
			   ->in(sprintf('phar://%s', $path));

		if ($input->getOption('search') !== null) {
			$finder->name($input->getOption('search'));
		}

		/* @var \Symfony\Component\Finder\SplFileInfo $file */
		foreach ($finder as $file) {
			$output->writeln($file->getRelativePathname());
		}
	}

}