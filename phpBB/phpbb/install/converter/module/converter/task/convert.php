<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */
namespace phpbb\install\converter\module\converter\task;
use phpbb\install\exception\user_interaction_required_exception;
/**
 * This class requests and validates database information from the user
 */
class convert extends \phpbb\install\task_base implements \phpbb\install\task_interface
{
	/**
	 * @var \phpbb\install\helper\install_helper
	 */
	protected $helper;
	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;
	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $io_handler;
	/**
	 * @var bool
	 */
	protected $converter;
	/**
	 * @var
	 */
	protected $phpbb_root_path;
	/**
	 * @var \phpbb\install\helper\container_factory
	 */
	protected $container_factory;
	/**
	 * @var \phpbb\language\language
	 */
	protected $language;
	/**
	 * convert constructor.
	 *
	 * @param \phpbb\install\converter\bin\converter              $converter
	 * @param \phpbb\install\helper\install_helper                $helper
	 * @param \phpbb\install\helper\config                        $install_config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface $iohandler
	 * @param \phpbb\install\helper\container_factory             $container_factory
	 * @param \phpbb\language\language                            $language
	 * @param                                                     $phpbb_root_path
	 */
	public function __construct(\phpbb\install\converter\bin\converter $converter, $helper,
		\phpbb\install\helper\config $install_config,
		\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
		\phpbb\install\helper\container_factory $container_factory,
		\phpbb\language\language $language, $phpbb_root_path)
	{
		$this->helper = $helper;
		$this->install_config = $install_config;
		$this->io_handler = $iohandler;
		$this->converter = $converter;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->container_factory = $container_factory;
		$this->language = $language;
		parent::__construct(true);
	}
	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		try
		{
			$this->helper->set_conversion_status(true);
			$yaml_queue = $this->helper->get_yaml_queue();
			$curr_index = $this->helper->get_file_index();
			if ($this->helper->get_conversion_status() && $curr_index < count($yaml_queue))
			{
				if (!$this->helper->get_chunk_status() || $this->helper->get_chunk_status() === null)
				{
					$this->helper->set_current_conversion_file($yaml_queue[$curr_index]);
					$this->io_handler->add_log_message('Loading..', 'Fetching next file');
					$this->helper->set_current_chunk(0);
					$this->helper->set_chunk_status(true);
					$log_msg = $this->language->lang('CF_LOG_CONVERTING') . $yaml_queue[$curr_index];
					$this->io_handler->set_task_count(1, true);
					$this->io_handler->set_progress($log_msg, 0.01); //Gives 1 % value at progress bar initially
					$this->io_handler->add_log_message($this->language->lang('CF_LOG_CONVERTING'), $log_msg);
					$this->io_handler->send_response();
				}
				else
				{
					$total_chunks = $this->helper->get_total_chunks();
					$chunk = $this->helper->get_current_chunk();
					$log_msg = $this->language->lang('CF_LOG_CONVERTING') . $yaml_queue[$curr_index] . " " . $this->language->lang('CF_LOG_PART') . "[ " . ($chunk + 1) . " ]";
					$this->io_handler->add_log_message($this->language->lang('CF_LOG_CONVERTING'), $log_msg);
					$this->io_handler->set_task_count($total_chunks);
					$this->io_handler->set_progress($log_msg, ($chunk + 1));
					$this->helper->set_current_chunk($chunk + 1);
					$this->io_handler->send_response();
				}
				$this->converter->begin_conversion($yaml_queue[$curr_index], $this->helper, $this->io_handler);
				if (!$this->helper->get_chunk_status())
				{
					$this->helper->next_file($curr_index);
					sleep(2); //sleeps 2 seconds to prevent abrupt change of progress bar.
					$this->io_handler->send_response();
				}
				/*
				The moment release_lock() is called, when js queries converter_status a continue status is issued
				causing a reload of the request, thus automatically moving to the next file
				*/
			}
		}
		catch(\Exception $e)
		{
			$this->io_handler->add_error_message('ERROR OCCURRED',$e->getMessage());
			$this->io_handler->add_log_message('Exception',$e->getMessage());
			$this->helper->set_conversion_status(false);
			$this->helper->set_error_state(true);
			$this->io_handler->send_response(true);
		}
	}
	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 0;
	}
	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return '';
	}
}