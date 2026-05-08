<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\migrations;

class v_0_3_0 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\acme\aisearch\migrations\v_0_2_0'];
	}

	public function effectively_installed()
	{
		return isset($this->config['acme_aisearch_semantic_strategy']);
	}

	public function update_data()
	{
		return [
			['config.add', ['acme_aisearch_semantic_strategy', 'proxy']],
			['config.add', ['acme_aisearch_embedding_query_enabled', 0]],
			['config.add', ['acme_aisearch_embedding_index_enabled', 0]],
		];
	}
}

