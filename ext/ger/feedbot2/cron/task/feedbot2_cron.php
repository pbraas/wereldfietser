<?php
/**
 *
 * Feed Bot 2. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedbot2\cron\task;

/**
 * Feed post bot cron task.
 */
class feedbot2_cron extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
	protected $cron_frequency = 1800; // Default to 30 minutes

	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \ger\feedbot2\classes\driver */
	protected $feedbot2;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config Config object
	 */
	public function __construct(\phpbb\config\config $config,  \ger\feedbot2\classes\driver $feedbot2)
	{
		$this->config = $config;
        $this->cron_frequency = $config['feedbot2_cron_frequency'];
		$this->feedbot2 = $feedbot2;
	}

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		// Run your cron actions here...
		$this->feedbot2->fetch_all();

		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
		$this->config->set('feedbot2_cron_last_run', time(), false);
	}

	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * For example, a cron task that prunes forums can only run when
	 * forum pruning is enabled.
	 *
	 * @return bool
	 */
	public function is_runnable()
	{
		return true;
	}

	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
        if ($this->cron_frequency > 0) 
        {
            return $this->config['feedbot2_cron_last_run'] < (time() - $this->cron_frequency);
        }
        return false;
	}
}
