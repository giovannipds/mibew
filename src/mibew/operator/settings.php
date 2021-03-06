<?php
/*
 * Copyright 2005-2013 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Import namespaces and classes of the core
use Mibew\Settings;
use Mibew\Style\ChatStyle;
use Mibew\Style\InvitationStyle;
use Mibew\Style\PageStyle;

// Initialize libraries
require_once(dirname(dirname(__FILE__)) . '/libs/init.php');
require_once(MIBEW_FS_ROOT . '/libs/operator.php');
require_once(MIBEW_FS_ROOT . '/libs/settings.php');
require_once(MIBEW_FS_ROOT . '/libs/cron.php');

$operator = check_login();
force_password($operator);
csrf_check_token();

$page = array(
    'agentId' => '',
    'errors' => array(),
);

// Load system configs
$options = array(
    'email',
    'title',
    'logo',
    'hosturl',
    'usernamepattern',
    'chattitle',
    'geolink',
    'geolinkparams',
    'sendmessagekey',
    'cron_key',
);

$params = array();
foreach ($options as $opt) {
    $params[$opt] = Settings::get($opt);
}

// Load styles configs
$styles_params = array(
    'chat_style' => ChatStyle::defaultStyle(),
    'page_style' => PageStyle::defaultStyle(),
);

$chat_style_list = ChatStyle::availableStyles();
$page_style_list = PageStyle::availableStyles();

if (Settings::get('enabletracking')) {
    $styles_params['invitation_style'] = InvitationStyle::defaultStyle();
    $invitation_style_list = InvitationStyle::availableStyles();
}

if (isset($_POST['email']) && isset($_POST['title']) && isset($_POST['logo'])) {
    $params['email'] = get_param('email');
    $params['title'] = get_param('title');
    $params['logo'] = get_param('logo');
    $params['hosturl'] = get_param('hosturl');
    $params['usernamepattern'] = get_param('usernamepattern');
    $params['chattitle'] = get_param('chattitle');
    $params['geolink'] = get_param('geolink');
    $params['geolinkparams'] = get_param('geolinkparams');
    $params['sendmessagekey'] = verify_param('sendmessagekey', "/^c?enter$/");
    $params['cron_key'] = get_param('cronkey');

    $styles_params['chat_style'] = verify_param("chat_style", "/^\w+$/", $styles_params['chat_style']);
    if (!in_array($styles_params['chat_style'], $chat_style_list)) {
        $styles_params['chat_style'] = $chat_style_list[0];
    }

    $styles_params['page_style'] = verify_param("page_style", "/^\w+$/", $styles_params['page_style']);
    if (!in_array($styles_params['page_style'], $page_style_list)) {
        $styles_params['page_style'] = $page_style_list[0];
    }

    if (Settings::get('enabletracking')) {
        $styles_params['invitation_style'] = verify_param(
            "invitation_style",
            "/^\w+$/",
            $styles_params['invitation_style']
        );
        if (!in_array($styles_params['invitation_style'], $invitation_style_list)) {
            $styles_params['invitation_style'] = $invitation_style_list[0];
        }
    }

    if ($params['email'] && !is_valid_email($params['email'])) {
        $page['errors'][] = getlocal("settings.wrong.email");
    }

    if ($params['geolinkparams']) {
        foreach (preg_split("/,/", $params['geolinkparams']) as $one_param) {
            $wrong_param = !preg_match(
                "/^\s*(toolbar|scrollbars|location|status|menubar|width|height|resizable)=\d{1,4}$/",
                $one_param
            );
            if ($wrong_param) {
                $page['errors'][] = "Wrong link parameter: \"$one_param\", "
                    . "should be one of 'toolbar, scrollbars, location, "
                    . "status, menubar, width, height or resizable'";
            }
        }
    }

    if (preg_match("/^[0-9A-z]*$/", $params['cron_key']) == 0) {
        $page['errors'][] = getlocal("settings.wrong.cronkey");
    }

    if (count($page['errors']) == 0) {
        // Update system settings
        foreach ($options as $opt) {
            Settings::set($opt, $params[$opt]);
        }
        Settings::update();

        // Update styles params
        ChatStyle::setDefaultStyle($styles_params['chat_style']);
        PageStyle::setDefaultStyle($styles_params['page_style']);
        if (Settings::get('enabletracking')) {
            InvitationStyle::setDefaultStyle($styles_params['invitation_style']);
        }

        // Redirect the user
        header("Location: " . MIBEW_WEB_ROOT . "/operator/settings.php?stored");
        exit;
    }
}

$page['formemail'] = to_page($params['email']);
$page['formtitle'] = to_page($params['title']);
$page['formlogo'] = to_page($params['logo']);
$page['formhosturl'] = to_page($params['hosturl']);
$page['formgeolink'] = to_page($params['geolink']);
$page['formgeolinkparams'] = to_page($params['geolinkparams']);
$page['formusernamepattern'] = to_page($params['usernamepattern']);
$page['formpagestyle'] = $styles_params['page_style'];
$page['availablePageStyles'] = $page_style_list;
$page['formchatstyle'] = $styles_params['chat_style'];
$page['formchattitle'] = to_page($params['chattitle']);
$page['formsendmessagekey'] = $params['sendmessagekey'];
$page['availableChatStyles'] = $chat_style_list;
$page['stored'] = isset($_GET['stored']);
$page['enabletracking'] = Settings::get('enabletracking');
$page['formcronkey'] = $params['cron_key'];

$page['cron_path'] = cron_get_uri($params['cron_key']);

$page['title'] = getlocal("settings.title");
$page['menuid'] = "settings";

if (Settings::get('enabletracking')) {
    $page['forminvitationstyle'] = $styles_params['invitation_style'];
    $page['availableInvitationStyles'] = $invitation_style_list;
}

$page = array_merge($page, prepare_menu($operator));

$page['tabs'] = setup_settings_tabs(0);

$page_style = new PageStyle(PageStyle::currentStyle());
$page_style->render('settings', $page);
