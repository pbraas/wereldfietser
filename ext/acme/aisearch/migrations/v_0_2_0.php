<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\migrations;

class v_0_2_0 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\acme\aisearch\migrations\v_0_1_1'];
	}

	public function effectively_installed()
	{
		return isset($this->config['acme_aisearch_semantic_enabled']);
	}

	public function update_data()
	{
		return [
			['config.add', ['acme_aisearch_search_mode', 'lexical']],
			['config.add', ['acme_aisearch_semantic_enabled', 0]],
			['config.add', ['acme_aisearch_hybrid_alpha', '0.35']],
			['config.add', ['acme_aisearch_semantic_top_k', 50]],
		];
	}
}

