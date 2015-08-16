<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint16 extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
    public function change()
    {

    }
    */

    /**
     * Migrate Up.
     */
    public function up()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-gf-finances' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        // create the orders table
        $table = $this->table('accounting_events');
        $table
              ->addColumn('user_id', 'integer')
              ->addColumn('event_type', 'enum', ['values' => ['singup','singup_bonus', 'talk_session', 'gift_card_puchase']])
              ->addColumn('income_credits', 'float')
              ->addColumn('withdrawal_credits', 'float')
              ->addColumn('ballance', 'float')
              ->addColumn('event_date', 'datetime')
              ->addIndex(['event_date'])
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-gf-finances' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }

        if ($this->hasTable('accounting_events')) {
            // remove the accounting_events table
            $this->dropTable('accounting_events');
        }
    }
}
