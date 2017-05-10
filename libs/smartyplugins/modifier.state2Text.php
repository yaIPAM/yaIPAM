<?php
/**
 * modifier.state2Text.php
 * Project: yaipam
 * User: ktammling
 * Date: 30.04.17
 * Time: 11:45
 */

function smarty_modifier_state2Text(int $ID): string {

	return stateToText($ID);

}