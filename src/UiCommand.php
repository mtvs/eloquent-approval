<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class UiCommand extends Command
{
	protected $signature = 'approval:ui';

	protected $description = 'Install the approval UI components';

	public function handle()
	{
		$filesystem = new Filesystem;

		$dir = resource_path('js/components/approval');

		$filesystem->makeDirectory($dir, 0755, true, true);

		$filesystem->copyDirectory(
			__DIR__.'/../stubs/ui',
			$dir
		);

		$this->info("The Approval UI components were copied successfully in the components dir.");
		$this->comment("Do not forget to register the components in your app.js file.");
	}
}