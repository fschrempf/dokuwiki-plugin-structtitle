<?php
/**
 * DokuWiki Plugin structtitle (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Frieder Schrempf <dev@fris.de>
 */

use dokuwiki\plugin\struct\meta\AccessTable;
use dokuwiki\plugin\struct\meta\Assignments;

class action_plugin_structtitle extends DokuWiki_Action_Plugin
{
    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     *
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this,
                                   'parser_render');
    }

    /**
     * [Custom event handler which performs action]
     *
     * Called for event: PARSER_METADATA_RENDER
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     *
     * @return void
     */
    public function parser_render(Doku_Event $event, $param)
    {
        global $ID;
        global $REV;

        $meta = &$event->data['current'];

        try {
            $assignments = Assignments::getInstance();
        } catch (StructException $e) {
            return false;
        }
        $tables = $assignments->getPageAssignments($ID);

        foreach ($tables as $table) {
            try {
                $schemadata = AccessTable::getPageAccess($table, $ID,
                                                         (int)$REV);
            } catch (StructException $ignored) {
                continue; // no such schema at this revision
            }

            $schemadata->optionSkipEmpty(true);
            $data = $schemadata->getData();

            /* Look for a field named 'title' in the struct data */
            if (!count($data) || !$data['title'])
                continue;

            $meta['title'] = $data['title']->getDisplayValue();
            break;
        }
    }
}
