<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TokenNotifiedAt extends AbstractMigration
{
    /**
     * Remember when the expiration notification was sent, so the daemon does not repeat it every cycle.
     */
    public function change(): void
    {
        $table = $this->table('token');
        $table->addColumn('notified_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Last refresh token expiration notification'])
            ->update();
    }
}
