<?php
/* For licensing terms, see /license.txt */

use ChamiloSession as Session;

/**
 * @package chamilo.glossary
 *
 * @author Christian Fasanando, initial version
 * @author Bas Wijnen import/export to CSV
 */
require_once __DIR__.'/../inc/global.inc.php';

$current_course_tool = TOOL_GLOSSARY;

// The section (tabs).
$this_section = SECTION_COURSES;

// Notification for unauthorized people.
api_protect_course_script(true);

// Additional javascripts.
$htmlHeadXtra[] = GlossaryManager::javascript_glossary();
$htmlHeadXtra[] = '<script>
function setFocus(){
    $("#glossary_title").focus();
}
$(document).ready(function () {
    setFocus();
});
</script>';

// Tracking
Event::event_access_tool(TOOL_GLOSSARY);

function sorter($item1, $item2)
{
    if ($item1[2] == $item2[2]) {
        return 0;
    }

    return $item1[2] < $item2[2] ? -1 : 1;
}

// Displaying the header
$action = isset($_GET['action']) ? Security::remove_XSS($_GET['action']) : '';
$currentUrl = api_get_self().'?'.api_get_cidreq();
$interbreadcrumb[] = ['url' => 'index.php?'.api_get_cidreq(), 'name' => get_lang('Glossary')];

$content = '';
$tool_name = '';
switch ($action) {
    case 'addglossary':
        if (!api_is_allowed_to_edit(null, true)) {
            api_not_allowed(true);
        }
        $tool_name = get_lang('Add');
        $form = new FormValidator(
            'glossary',
            'post',
            api_get_self().'?action=addglossary&'.api_get_cidreq()
        );
        // Setting the form elements
        $form->addElement('header', get_lang('TermAddNew'));
        if (api_get_configuration_value('save_titles_as_html')) {
            $form->addHtmlEditor(
                'name',
                get_lang('TermName'),
                false,
                false,
                ['ToolbarSet' => 'Minimal']
            );
        } else {
            $form->addElement('text', 'name', get_lang('TermName'), ['id' => 'glossary_title']);
        }

        $form->addElement(
            'html_editor',
            'description',
            get_lang('TermDefinition'),
            null,
            ['ToolbarSet' => 'Glossary', 'Height' => '300']
        );
        $form->addButtonCreate(get_lang('TermAddButton'), 'SubmitGlossary');
        // setting the rules
        $form->addRule('name', get_lang('ThisFieldIsRequired'), 'required');
        // The validation or display
        if ($form->validate()) {
            $check = Security::check_token('post');
            if ($check) {
                $values = $form->exportValues();
                GlossaryManager::save_glossary($values);
            }
            Security::clear_token();
            header('Location: '.$currentUrl);
            exit;
        } else {
            $token = Security::get_token();
            $form->addElement('hidden', 'sec_token');
            $form->setConstants(['sec_token' => $token]);
            $content = Display::toolbarAction(
                'add_glossary',
                [
                    Display::url(
                        Display::return_icon('back.png', get_lang('Back'), [], ICON_SIZE_MEDIUM),
                        api_get_self().'?'.api_get_cidreq()
                    ),
                ]
            );
            $content .= $form->returnForm();
        }
        break;
    case 'edit_glossary':
        if (!api_is_allowed_to_edit(null, true)) {
            api_not_allowed(true);
        }
        $tool_name = get_lang('Edit');
        $glossaryId = isset($_GET['glossary_id']) ? (int) $_GET['glossary_id'] : 0;
        if (!empty($glossaryId)) {
            // initiate the object
            $form = new FormValidator(
                'glossary',
                'post',
                api_get_self().'?action=edit_glossary&glossary_id='.$glossaryId.'&'.api_get_cidreq()
            );
            // Setting the form elements
            $form->addElement('header', get_lang('TermEdit'));
            $form->addElement('hidden', 'glossary_id');
            if (api_get_configuration_value('save_titles_as_html')) {
                $form->addHtmlEditor(
                    'name',
                    get_lang('TermName'),
                    false,
                    false,
                    ['ToolbarSet' => 'Minimal']
                );
            } else {
                $form->addElement('text', 'name', get_lang('TermName'), ['id' => 'glossary_title']);
            }

            $form->addElement(
                'html_editor',
                'description',
                get_lang('TermDefinition'),
                null,
                ['ToolbarSet' => 'Glossary', 'Height' => '300']
            );

            // setting the defaults
            $glossary_data = GlossaryManager::get_glossary_information($glossaryId);

            // Date treatment for timezones
            if (!empty($glossary_data['insert_date'])) {
                $glossary_data['insert_date'] = Display::dateToStringAgoAndLongDate($glossary_data['insert_date']);
            } else {
                $glossary_data['insert_date'] = '';
            }

            if (!empty($glossary_data['update_date'])) {
                $glossary_data['update_date'] = Display::dateToStringAgoAndLongDate($glossary_data['update_date']);
            } else {
                $glossary_data['update_date'] = '';
            }

            $form->addLabel(get_lang('CreationDate'), $glossary_data['insert_date']);
            $form->addLabel(get_lang('UpdateDate'), $glossary_data['update_date']);

            $form->addButtonUpdate(get_lang('TermUpdateButton'), 'SubmitGlossary');
            $form->setDefaults($glossary_data);

            // setting the rules
            $form->addRule('name', get_lang('ThisFieldIsRequired'), 'required');

            // The validation or display
            if ($form->validate()) {
                $check = Security::check_token('post');
                if ($check) {
                    $values = $form->exportValues();
                    GlossaryManager::update_glossary($values);
                }
                Security::clear_token();
                header('Location: '.$currentUrl);
                exit;
            } else {
                $token = Security::get_token();
                $form->addElement('hidden', 'sec_token');
                $form->setConstants(['sec_token' => $token]);
                $content = Display::toolbarAction(
                    'edit_glossary',
                    [
                        Display::url(
                            Display::return_icon('back.png', get_lang('Back'), [], ICON_SIZE_MEDIUM),
                            api_get_self().'?'.api_get_cidreq()
                        ),
                    ]
                );
                $content .= $form->returnForm();
            }
        }
        break;
    case 'delete_glossary':
        if (!api_is_allowed_to_edit(null, true)) {
            api_not_allowed(true);
        }
        GlossaryManager::delete_glossary($_GET['glossary_id']);
        Security::clear_token();
        header('Location: '.$currentUrl);
        exit;
        break;
    case 'moveup':
        //GlossaryManager::move_glossary('up',$_GET['glossary_id']); //actions not available
        GlossaryManager::display_glossary();
        break;
    case 'movedown':
        //GlossaryManager::move_glossary('down',$_GET['glossary_id']); //actions not available
        GlossaryManager::display_glossary();
        break;
    case 'import':
        if (!api_is_allowed_to_edit(null, true)) {
            api_not_allowed(true);
        }
        $tool_name = get_lang('ImportGlossary');
        $form = new FormValidator(
            'glossary',
            'post',
            api_get_self().'?action=import&'.api_get_cidreq()
        );
        $form->addHeader('header', get_lang('ImportGlossary'));
        $form->addElement('file', 'file', get_lang('ImportCSVFileLocation'));
        $form->addElement('checkbox', 'replace', null, get_lang('DeleteAllGlossaryTerms'));
        $form->addElement('checkbox', 'update', null, get_lang('UpdateExistingGlossaryTerms'));
        $form->addButtonImport(get_lang('Import'), 'SubmitImport');
        $content = $form->returnForm();

        $content .= get_lang('CSVMustLookLike').' ('.get_lang('MandatoryFields').')';
        $content .= '<pre>
                <strong>term</strong>;<strong>definition</strong>;
                "Hello";"Hola";
                "Goodbye";"Adiós";
        </pre>';

        if ($form->validate()) {
            $termsDeleted = [];
            //this is a bad idea //jm
            if (isset($_POST['replace']) && $_POST['replace']) {
                foreach (GlossaryManager::get_glossary_terms() as $term) {
                    if (!GlossaryManager::delete_glossary($term['id'], false)) {
                        Display::addFlash(
                            Display::return_message(get_lang('CannotDeleteGlossary').':'.$term['id'], 'error')
                        );
                    } else {
                        $termsDeleted[] = $term['name'];
                    }
                }
            }

            $updateTerms = isset($_POST['update']) && $_POST['update'] ? true : false;
            $data = Import::csvToArray($_FILES['file']['tmp_name']);
            $goodList = [];
            $updatedList = [];
            $addedList = [];
            $badList = [];
            $doubles = [];
            $added = [];
            $termsPerKey = [];

            if ($data) {
                $termsToAdd = [];
                foreach ($data as $item) {
                    if (!isset($item['term'])) {
                        continue;
                    }
                    $items = [
                        'name' => $item['term'],
                        'description' => $item['definition'],
                    ];
                    $termsToAdd[] = $items;
                    $termsPerKey[$item['term']] = $items;
                }

                if (empty($termsToAdd)) {
                    Display::addFlash(
                        Display::return_message(get_lang('NothingToAdd'), 'warning')
                    );
                    header('Location: '.$currentUrl);
                    exit;
                }

                $repeatItems = array_count_values(array_column($termsToAdd, 'name'));
                foreach ($repeatItems as $item => $count) {
                    if ($count > 1) {
                        $doubles[] = $item;
                    }
                }

                $uniqueTerms = array_unique(array_keys($repeatItems));

                foreach ($uniqueTerms as $itemTerm) {
                    $item = $termsPerKey[$itemTerm];

                    if ($updateTerms) {
                        $glossaryInfo = GlossaryManager::get_glossary_term_by_glossary_name($item['name']);

                        if (!empty($glossaryInfo)) {
                            $glossaryInfo['description'] = $item['description'];
                            GlossaryManager::update_glossary($glossaryInfo, false);
                            $updatedList[] = $item['name'];
                        } else {
                            $result = GlossaryManager::save_glossary($item, false);
                            if ($result) {
                                $addedList[] = $item['name'];
                            } else {
                                $badList[] = $item['name'];
                            }
                        }
                    } else {
                        $result = GlossaryManager::save_glossary($item, false);
                        if ($result) {
                            $addedList[] = $item['name'];
                        } else {
                            $badList[] = $item['name'];
                        }
                    }
                }
            }

            if (count($termsDeleted) > 0) {
                Display::addFlash(
                    Display::return_message(get_lang("TermDeleted").': '.implode(', ', $termsDeleted))
                );
            }

            if (count($updatedList) > 0) {
                Display::addFlash(
                    Display::return_message(get_lang("TermsUpdated").': '.implode(', ', $updatedList))
                );
            }

            if (count($addedList) > 0) {
                Display::addFlash(
                    Display::return_message(get_lang("TermsAdded").': '.implode(', ', $addedList))
                );
            }

            if (count($badList) > 0) {
                Display::addFlash(
                    Display::return_message(
                        get_lang("GlossaryTermAlreadyExists").': '.implode(', ', $badList),
                        'error'
                    )
                );
            }

            if (count($doubles) > 0) {
                Display::addFlash(
                    Display::return_message(
                        get_lang("TermsDuplicatedInFile").': '.implode(', ', $doubles),
                        'warning'
                    )
                );
            }

            header('Location: '.$currentUrl);
            exit;
        }
        break;
    case 'export':
        if (!api_is_allowed_to_edit(null, true)) {
            api_not_allowed(true);
        }
        $data = GlossaryManager::get_glossary_data(
            0,
            GlossaryManager::get_number_glossary_terms(api_get_session_id()),
            0,
            'ASC'
        );

        usort($data, 'sorter');
        $list = [];
        $list[] = ['term', 'definition'];
        $allowStrip = api_get_configuration_value('allow_remove_tags_in_glossary_export');
        foreach ($data as $line) {
            $definition = $line[1];
            if ($allowStrip) {
                $definition = strip_tags($definition);
            }
            $list[] = [$line[0], $definition];
        }
        $filename = 'glossary_course_'.api_get_course_id();
        Export::arrayToCsv($list, $filename);
        break;
    case 'export_to_pdf':
        if (!api_is_allowed_to_edit(null, true)) {
            api_not_allowed(true);
        }
        GlossaryManager::export_to_pdf();
        break;
    case 'changeview':
        if (in_array($_GET['view'], ['list', 'table'])) {
            Session::write('glossary_view', $_GET['view']);
        } else {
            $view = Session::read('glossary_view');
            $defaultView = api_get_configuration_value('default_glossary_view');
            if (empty($defaultView)) {
                $defaultView = 'table';
            }
            if (empty($view)) {
                Session::write('glossary_view', $defaultView);
            }
        }
        header('Location: '.$currentUrl);
        exit;
        break;
    case 'export_documents':
        GlossaryManager::movePdfToDocuments();
        header('Location: '.$currentUrl);
        exit;
        break;
    default:
        $tool_name = get_lang('List');
        $content = GlossaryManager::display_glossary();
        break;
}

Display::display_header($tool_name);

// Tool introduction
Display::display_introduction_section(TOOL_GLOSSARY);

echo $content;

Display::display_footer();
