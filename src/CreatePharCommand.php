<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Finder\Finder;

class CreatePharCommand extends Command
{

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		// @todo enable excluding of files
		$this->setName('phar:create')
			 ->setDescription('Creates a phar archive from given folders')
			 ->setDefinition(array(
				new InputOption('name', 'N', InputOption::VALUE_OPTIONAL, 'Name of the phar', 'lib.phar'),
				new InputArgument('folders', InputArgument::IS_ARRAY, 'Folders to pack into the phar', array('.'))
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
		$this->ensurePharCreateable();

		$name    = $input->getOption('name');
		$name    = $this->sanitizeName($name);
		$folders = $input->getArgument('folders');

		$this->createPhar($name, $folders);
	}

	/**
	 * Ensures its possible to create a phar
	 *
	 * @throws BadMethodCallException
	 *
	 * @return void
	 */
	protected function ensurePharCreateable()
	{
		if (ini_get('phar.readonly') == 1) {
			throw new \BadMethodCallException('phar.readyonly is set to 1');
		}
	}

	/**
	 * Ensures that the name has a .phar at the end
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function sanitizeName($name)
	{
		$parts = explode('.', $name);
		if (array_pop($parts) !== 'phar') {
			return sprintf('%s.phar', $name);
		}

		return $name;
	}

	/**
	 * Creates a phar
	 *
	 * @param string $name name of the phar
	 * @param array $folders folders to search for files
	 *
	 * @return bool
	 */
	protected function createPhar($name, array $folders)
	{
		// Search for PHP Files to phar (used by finder so we can exclude files later more easily)
		$files = $this->getFilesToPhar($folders);

		$phar = new \Phar($name);
		$phar->startBuffering();

		/** @var $file \Symfony\Component\Finder\SplFileInfo */
		foreach ($files as $file) {
			$phar->addFile($file->getRealPath(), $file->getPathname());
		}

		$phar->setStub($this->createPharStub());
		$phar->stopBuffering();
	}

	/**
	 * Returns a stub for the phar
	 *
	 * @return string
	 */
	protected function createPharStub()
	{
		$stub = <<< 'EOF'
#!/usr/bin/env php
<?php

Phar::mapPhar();

__HALT_COMPILER();
EOF;

		return $stub;
	}

	/**
	 * Returns array of files to phar
	 *
	 * @param array $folders
	 *
	 * @return \Symfony\Component\Finder\SplFileInfo[]
	 */
	protected function getFilesToPhar(array $folders)
	{
		$finder = new Finder();
		return $finder->files()
					  ->ignoreVCS(true)
					  ->name('*.php')
					  ->in($folders);
	}

}