<?php

declare(strict_types=1);

/**
 * This file is part of the CSASAuthorize package
 *
 * https://github.com/Spoje-NET/csas-authorize
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\CSas\Ui;

require_once '../vendor/autoload.php';
require_once './init.php';

use SpojeNet\CSas\DeveloperPortalImporter;

// Handle form submission
if (\Ease\WebPage::isPosted()) {
    $importer = new DeveloperPortalImporter();
    $success = false;
    
    // Check if JSON file was uploaded
    if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $success = $importer->importFromJson($_FILES['json_file']['tmp_name']);
            if ($success) {
                $app = $importer->getApplication();
                WebPage::singleton()->redirect('application.php?id=' . $app->getMyKey());
            }
        } catch (\RuntimeException $e) {
            WebPage::singleton()->addStatusMessage($e->getMessage(), 'error');
        }
    }
    // Check if JSON data was pasted
    elseif (!empty($_POST['json_data'])) {
        try {
            $jsonData = json_decode($_POST['json_data'], true);
            if ($jsonData === null) {
                throw new \RuntimeException(_('Invalid JSON format'));
            }
            
            $success = $importer->importFromArray($jsonData);
            if ($success) {
                $app = $importer->getApplication();
                WebPage::singleton()->redirect('application.php?id=' . $app->getMyKey());
            }
        } catch (\RuntimeException $e) {
            WebPage::singleton()->addStatusMessage($e->getMessage(), 'error');
        }
    } else {
        WebPage::singleton()->addStatusMessage(_('Please upload a JSON file or paste JSON data'), 'warning');
    }
}

// Create the page
WebPage::singleton()->addItem(new PageTop(_('Import Application from Developer Portal')));

$container = WebPage::singleton()->container ?? new \Ease\TWB5\Container();

// Add breadcrumb
$breadcrumb = new \Ease\TWB5\Row();
$breadcrumb->addColumn(12, [
    new \Ease\Html\ATag('index.php', _('Applications'), ['class' => 'btn btn-outline-primary btn-sm']),
    ' → ',
    _('Import from Developer Portal')
]);
$container->addItem($breadcrumb);

// Add import form
$importForm = new \SpojeNet\CSas\Ui\ImportForm();
$container->addItem($importForm);

// Add manual export guide
$guideSection = new \Ease\Html\DivTag(null, ['class' => 'mt-5']);
$guideSection->addItem(new \Ease\Html\H3Tag(_('Manual Export Guide')));

$guide = new \Ease\Html\DivTag();
$guide->addItem(new \Ease\Html\PTag(_('Currently, CSAS Developer Portal does not provide automated export functionality. To import your application data:')));

$steps = new \Ease\Html\OlTag([
    new \Ease\Html\LiTag(_('Visit your application in CSAS Developer Portal: ') . 
        new \Ease\Html\ATag('https://developers.erstegroup.com/portal/organizations/vitezslav-dvorak/applications', 
            'https://developers.erstegroup.com/portal/organizations/vitezslav-dvorak/applications',
            ['target' => '_blank', 'class' => 'text-primary'])),
    new \Ease\Html\LiTag(_('Copy the following information from each application:')),
    new \Ease\Html\UlTag([
        new \Ease\Html\LiTag(_('Application Name')),
        new \Ease\Html\LiTag(_('Application ID (UUID)')),
        new \Ease\Html\LiTag(_('Logo URL (if available)')),
        new \Ease\Html\LiTag(_('Sandbox Client ID')),
        new \Ease\Html\LiTag(_('Sandbox Client Secret')),
        new \Ease\Html\LiTag(_('Sandbox API Key')),
        new \Ease\Html\LiTag(_('Production Client ID')),
        new \Ease\Html\LiTag(_('Production Client Secret')),
        new \Ease\Html\LiTag(_('Production API Key')),
        new \Ease\Html\LiTag(_('Redirect URIs for both environments'))
    ]),
    new \Ease\Html\LiTag(_('Format the data according to the JSON structure shown above')),
    new \Ease\Html\LiTag(_('Use the import form to add the application to CSAS Authorize'))
]);

$guide->addItem($steps);
$guideSection->addItem($guide);

// Add API suggestion
$apiNote = new \Ease\Html\DivTag(null, ['class' => 'alert alert-info mt-3']);
$apiNote->addItem(new \Ease\Html\StrongTag(_('Future Enhancement: ')));
$apiNote->addItem(_('We recommend that CSAS adds an export API or export button to the Developer Portal to automate this process. This would eliminate manual copying and reduce the risk of errors.'));
$guideSection->addItem($apiNote);

$container->addItem($guideSection);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
