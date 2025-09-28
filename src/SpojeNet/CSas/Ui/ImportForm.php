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

use Ease\Html\InputFileTag;
use Ease\Html\TextareaTag;
use Ease\TWB5\SubmitButton;
use Ease\Html\DivTag;
use Ease\Html\H3Tag;
use Ease\Html\PTag;
use Ease\Html\PreTag;
use SpojeNet\CSas\DeveloperPortalImporter;

/**
 * Form for importing application data from CSAS Developer Portal
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ImportForm extends \Ease\Html\Form
{
    public function __construct()
    {
        parent::__construct(['method' => 'POST', 'enctype' => 'multipart/form-data']);
        $this->setTagClass('form');
        
        $this->addItem(new H3Tag(_('Import from CSAS Developer Portal')));
        
        $this->addItem(new PTag(_('You can import application data from CSAS Developer Portal in two ways:')));
        
        // Method 1: JSON File Upload
        $fileSection = new DivTag();
        $fileSection->addItem(new \Ease\Html\H4Tag('1. ' . _('Upload JSON File')));
        $fileSection->addItem(new PTag(_('If you have exported your application data as JSON from Developer Portal:')));
        
        $fileInput = new InputFileTag('json_file');
        $fileInput->setTagProperties(['accept' => '.json,application/json']);
        $fileSection->addItem($fileInput);
        
        $this->addItem($fileSection);
        
        // Method 2: Manual JSON Paste
        $manualSection = new DivTag();
        $manualSection->addItem(new \Ease\Html\H4Tag('2. ' . _('Paste JSON Data')));
        $manualSection->addItem(new PTag(_('Copy and paste your application data in JSON format:')));
        
        $jsonTextarea = new TextareaTag('json_data');
        $jsonTextarea->setTagProperties([
            'rows' => '15',
            'placeholder' => _('Paste your JSON data here...'),
            'class' => 'form-control font-monospace'
        ]);
        $manualSection->addItem($jsonTextarea);
        
        $this->addItem($manualSection);
        
        // Submit button
        $this->addItem(new SubmitButton(_('Import Application'), 'success'));
        
        // Example JSON structure
        $exampleSection = new DivTag();
        $exampleSection->addItem(new \Ease\Html\H4Tag(_('Expected JSON Format')));
        $exampleSection->addItem(new PTag(_('Your JSON data should follow this structure:')));
        
        $exampleJson = new PreTag(DeveloperPortalImporter::getJsonExample());
        $exampleJson->setTagClass('bg-light p-3 border rounded');
        $exampleSection->addItem($exampleJson);
        
        $this->addItem($exampleSection);
        
        // Instructions for manual export
        $instructionsSection = new DivTag();
        $instructionsSection->addItem(new \Ease\Html\H4Tag(_('How to Export from Developer Portal')));
        $instructionsSection->addItem(new \Ease\Html\UlTag([
            new \Ease\Html\LiTag(_('1. Log in to CSAS Developer Portal')),
            new \Ease\Html\LiTag(_('2. Navigate to your application details')),
            new \Ease\Html\LiTag(_('3. Copy the application ID, client credentials, and API keys')),
            new \Ease\Html\LiTag(_('4. Format the data according to the JSON structure above')),
            new \Ease\Html\LiTag(_('5. Use this import form to add the application to CSAS Authorize'))
        ]));
        
        $this->addItem($instructionsSection);
        
        // Warning note
        $warningDiv = new DivTag(null, ['class' => 'alert alert-warning mt-3']);
        $warningDiv->addItem(new \Ease\Html\StrongTag(_('Security Note: ')));
        $warningDiv->addItem(_('Make sure to keep your client secrets and API keys secure. Only import data from trusted sources.'));
        $this->addItem($warningDiv);
    }
}
