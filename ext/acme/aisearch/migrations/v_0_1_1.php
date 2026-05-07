<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\migrations;

class v_0_1_1 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\acme\aisearch\migrations\v_0_1_0'];
	}

	public function effectively_installed()
	{
		return isset($this->config['acme_aisearch_cron_last_run']);
	}

	public function update_data()
	{
		return [
			// Timestamp of last successful cron run (0 = never run)
			['config.add', ['acme_aisearch_cron_last_run', 0]],
			// Number of queue rows to process per cron invocation
			['config.add', ['acme_aisearch_batch_size', 25]],
		];
	}
}

