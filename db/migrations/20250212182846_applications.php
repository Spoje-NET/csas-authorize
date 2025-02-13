<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Applications extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('application');
        $table->addColumn('uuid', 'uuid')
              ->addColumn('name', 'string', ['limit' => 255])  
              ->addColumn('logo', 'string', ['limit' => 255])  
              ->addColumn('sandbox_client_id', 'string', ['limit' => 255])
              ->addColumn('sandbox_client_secret', 'string', ['limit' => 255])
              ->addColumn('sandbox_redirect_uri', 'string', ['limit' => 255])
              ->addColumn('production_client_id', 'string', ['limit' => 255])
              ->addColumn('production_client_secret', 'string', ['limit' => 255])
              ->addColumn('production_redirect_uri', 'string', ['limit' => 255])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->create();
    }
}
