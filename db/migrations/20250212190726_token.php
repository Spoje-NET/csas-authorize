<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Token extends AbstractMigration
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
        $table = $this->table('token');
        $table->addColumn('application_id', 'integer', ['null'=> false])
              ->addColumn('environment', 'enum', ['values' => ['sandbox', 'production']])
              ->addColumn('access_token', 'string', ['limit' => 550])
              ->addColumn('refresh_token', 'string', ['limit' => 550])
              ->addColumn('expires_in', 'integer')
              ->addColumn('scope', 'string', ['limit' => 255])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('uuid', 'uuid')
              ->create();
    }
}
