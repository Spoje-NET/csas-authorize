<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ApplicationEmail extends AbstractMigration
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
        // Add email and last_send columns to the application table
        $table = $this->table('application');
        $table->addColumn('email', 'string', ['null' => true, 'default' => null, 'limit' => 255, 'comment' => 'Email address for sending renewal links'])
              ->addColumn('last_send', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Last send date and time'])
              ->update();
    }
}
