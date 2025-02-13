<?php

declare(strict_types=1);

/**
 * This file is part of the CSASAuthorize  package
 *
 * https://github.com/Spoje-NET/csas-authorize
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\CSas;

/**
 * Description of Application.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Application extends \Ease\SQL\Engine {

    public string $myTable = 'application';

    public static function getImage(string $appUuid): string {
        return 'https://webapi.developers.erstegroup.com/api/v1/file-manager/files2/' . $appUuid . '/image/small';
    }

    public function takeData(array $data): int {
        unset($data['class']);
        return parent::takeData($data);
    }
}
