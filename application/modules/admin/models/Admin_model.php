<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

    private $_limit,
            $_pageNumber,
            $_offset;
    /**
     * Admin_model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->auth = $this->load->database('auth', TRUE);

        if (!$this->wowmodule->getACPStatus())
            redirect(base_url(),'refresh');
    }

    public function setLimit($limit)
    {
        $this->_limit = $limit;
    }

    public function setPageNumber($pageNumber)
    {
        $this->_pageNumber = $pageNumber;
    }

    public function setOffset($offset)
    {
        $this->_offset = $offset;
    }

    public function getAllAccounts()
    {
        $this->auth->from('account');
        return $this->auth->count_all_results();
    }

    public function AccountsList()
    {
        return $this->auth->select('*')->limit($this->_pageNumber, $this->_offset)->get('account')->result();
    }

    public function insertDonationAjax($name, $price, $tax, $points)
    {
        $data = array(
            'name' => $name,
            'price' => $price,
            'tax' => $price,
            'points' => $price
        );

        $this->db->insert('donate', $data);
    }

    public function deleteDonationAjax($id)
    {
        $this->db->where('id', $id)->delete('donate');
    }

    public function getAdminCharactersList($multirealm)
    {
        $this->multirealm = $multirealm;
        return $this->multirealm->select('guid, account, name')->order_by('name', 'ASC')->get('characters');
    }

    public function insertBanChar($id, $reason, $multirealm, $idrealm)
    {
        $date       = $this->wowgeneral->getTimestamp();
        $idsession  = $this->session->userdata('wow_sess_id');

        if (empty($reason))
            $reason = $this->lang->line('log_banned');

        $data2 = array(
            'guid' => $id,
            'bandate,' => $date,
            'unbandate' => $date,
            'bannedby' => $idsession,
            'banreason' => $reason,
            'active' => '1'
        );

        $this->multirealm = $multirealm;
        $this->multirealm->insert('character_banned', $data2);

        $data1 = array(
            'idchar' => $id,
            'annotation' => $this->lang->line('alert_banned_reason').' '.$reason,
            'date' => $date,
            'realmid' => $idrealm
        );

        $this->db->insert('chars_annotations', $data1);

        redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm,'refresh');
    }

    public function insertCustomizeChar($id, $multirealm, $idrealm)
    {
        if ($this->wowrealm->getCharActive($id, $multirealm) == '1')
            redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm.'?char','refresh');

        $date       = $this->wowgeneral->getTimestamp();
        $annotation = $this->lang->line('log_customization');

        $data = array(
            'idchar' => $id,
            'annotation' => $annotation,
            'date' => $date,
            'realmid' => $idrealm
        );

        $this->db->insert('chars_annotations', $data);

        $this->multirealm = $multirealm;
        $this->multirealm->set('at_login', '8')
                ->where('guid', $id)
                ->update('characters');

        redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm,'refresh');
    }

    public function getDonateListAjax()
    {
        return $this->db->select('*')->order_by('id', 'ASC')->get('donate');
    }

    public function updateDonationAjax($id, $name, $column)
    {
        $this->db->set($column, $name)->where('id', $id)->update('donate');
    }

    public function delSpecifyDonation($id)
    {
        $this->db->where('id', $id)->delete('donate');

        redirect(base_url('admin/donate'),'refresh');
    }

    public function insertDonation($name, $price, $tax, $points)
    {
        $data = array(
            'name' => $name,
            'price' => $price,
            'tax' => $tax,
            'points' => $points,
        );

        $this->db->insert('donate', $data);

        redirect(base_url('admin/donate'),'refresh');
    }

    public function getUserHistoryDonate($id)
    {
        return $this->db->select('*')->where('user_id', $id)->order_by('id', 'DESC')->get('donate_logs');
    }

    public function getDonateStatus($id)
    {
        switch ($id) {
            case 0: return $this->lang->line('status_donate_cancell'); break;
            case 1: return $this->lang->line('status_donate_complete'); break;
        }
    }

    public function insertChangeFactionChar($id, $multirealm, $idrealm)
    {
        if ($this->wowrealm->getCharActive($id, $multirealm) == '1')
            redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm.'?char','refresh');

        $date       = $this->wowgeneral->getTimestamp();
        $annotation = $this->lang->line('log_change_faction');

        $data = array(
            'idchar' => $id,
            'annotation' => $annotation,
            'date' => $date,
            'realmid' => $idrealm
        );

        $this->db->insert('chars_annotations', $data);

        $this->multirealm = $multirealm;
        $this->multirealm->set('at_login', '64')->where('guid', $id)->update('characters');

        redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm,'refresh');
    }

    public function insertChangeRaceChar($id, $multirealm, $idrealm)
    {
        if ($this->wowrealm->getCharActive($id, $multirealm) == '1')
            redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm.'?char','refresh');

        $date       = $this->wowgeneral->getTimestamp();
        $annotation = $this->lang->line('log_change_race');

        $data = array(
            'idchar' => $id,
            'annotation' => $annotation,
            'date' => $date,
            'realmid' => $idrealm
        );

        $this->db->insert('chars_annotations', $data);

        $this->multirealm = $multirealm;
        $this->multirealm->set('at_login', '128')->where('guid', $id)->update('characters');

        redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm,'refresh');
    }

    public function insertUnbanChar($id, $multirealm, $idrealm)
    {
        $this->multirealm = $multirealm;
        $this->multirealm->where('guid', $id)->delete('character_banned');

        $date       = $this->wowgeneral->getTimestamp();
        $annotation = $this->lang->line('log_unbanned');

        $data = array(
            'idchar' => $id,
            'annotation' => $annotation,
            'date' => $date,
            'realmid' => $idrealm
        );

        $this->db->insert('chars_annotations', $data);

        redirect(base_url().'admin/managecharacter/'.$id.'/'.$idrealm,'refresh');
    }

    public function insertCharRename($id, $name, $multirealm, $realm)
    {
        if ($this->wowrealm->getCharActive($id, $multirealm) == '1')
            redirect(base_url().'admin/managecharacter/'.$id.'/'.$realm.'?char','refresh');

        if ($this->wowrealm->getCharNameAlreadyExist($name, $multirealm)->num_rows())
            redirect(base_url().'admin/managecharacter/'.$id.'/'.$realm.'?name','refresh');

        $date       = $this->wowgeneral->getTimestamp();
        $annotation = $this->lang->line('log_new_name').' -> '.$name.' | '.$this->lang->line('log_old_name').' -> '.$this->wowrealm->getCharName($id, $multirealm);

        $data = array(
            'idchar' => $id,
            'annotation' => $annotation,
            'date' => $date,
            'realmid' => $realm
        );

        $this->db->insert('chars_annotations', $data);

        $this->multirealm = $multirealm;
        $this->multirealm->set('name', $name)->where('guid', $id)->update('characters');

        redirect(base_url().'admin/managecharacter/'.$id.'/'.$realm,'refresh');
    }

    public function insertChangeLevelChar($id, $level, $multirealm, $realm)
    {
        if ($this->wowrealm->getCharActive($id, $multirealm) == '1')
            redirect(base_url().'admin/managecharacter/'.$id.'/'.$realm.'?char','refresh');

        $date       = $this->wowgeneral->getTimestamp();
        $annotation = $this->lang->line('log_new_level').' -> '.$level.' | '.$this->lang->line('log_old_level').' -> '.$this->wowrealm->getCharLevel($id, $multirealm);

        $data = array(
            'idchar' => $id,
            'annotation' => $annotation,
            'date' => $date,
            'realmid' => $realm
        );

        $this->db->insert('chars_annotations', $data);

        $this->multirealm = $multirealm;
        $this->multirealm->set('level', $level)->where('guid', $id)->update('characters');

        redirect(base_url().'admin/managecharacter/'.$id.'/'.$realm,'refresh');
    }

    public function getAnnotationsSpecifyChar($id, $realm)
    {
        return $this->db->select('*')->where('idchar', $id)->where('realmid', $realm)->order_by('id', 'DESC')->get('chars_annotations');
    }

    public function insertRankAcc($id, $gmlevel)
    {
        $data = array(
            'id' => $id,
            'gmlevel' => $gmlevel,
            'RealmID' => '-1',
        );

        $this->auth->insert('account_access', $data);

        $date   = $this->wowgeneral->getTimestamp();
        $reason = $this->lang->line('log_gm_assigned');

        $data = array(
            'iduser' => $id,
            'annotation' => $reason,
            'date' => $date,
        );

        $this->db->insert('users_annotations', $data);

        redirect(base_url().'admin/manageaccount/'.$id,'refresh');
    }

    public function getAnnotationsSpecify($id)
    {
        return $this->db->select('*')->where('iduser', $id)->get('users_annotations');
    }

    public function inserUnBanAcc($id)
    {
        $date = $this->wowgeneral->getTimestamp();

        if (empty($reason))
            $reason = $this->lang->line('log_unbanned');

        $data = array(
            'iduser' => $id,
            'annotation' => $reason,
            'date' => $date,
        );

        $this->db->insert('users_annotations', $data);

        $this->auth->where('id', $id)->delete('account_banned');

        if ($this->wowgeneral->getExpansionAction() == 2)
            $this->auth->where('id', $id)->delete('battlenet_account_bans');

        redirect(base_url().'admin/manageaccount/'.$id,'refresh');
    }

    public function removeRankAcc($id)
    {
        $this->auth->where('id', $id)->delete('account_access');

        $date   = $this->wowgeneral->getTimestamp();
        $reason = $this->lang->line('log_gm_removed');

        $data = array(
            'iduser' => $id,
            'annotation' => $reason,
            'date' => $date,
        );

        $this->db->insert('users_annotations', $data);

        redirect(base_url().'admin/manageaccount/'.$id,'refresh');
    }

    public function insertBanAcc($iduser, $reason)
    {
        $date = $this->wowgeneral->getTimestamp();
        $id   = $this->session->userdata('wow_sess_id');

        if (empty($reason))
            $reason = $this->lang->line('log_banned');

        $data1 = array(
            'iduser' => $iduser,
            'annotation' => $reason,
            'date' => $date,
        );

        $this->db->insert('users_annotations', $data1);

        $data2 = array(
            'id' => $iduser,
            'bandate' => $date,
            'unbandate' => $date,
            'bannedby' => $id,
            'banreason' => $reason,
        );

        $this->auth->insert('account_banned', $data2);

        if ($this->wowgeneral->getExpansionAction() == 2)
            $this->auth->insert('battlenet_account_bans', $data2);

        redirect(base_url().'admin/manageaccount/'.$iduser,'refresh');
    }

    public function getBanCount()
    {
        return $this->auth->select('id')->get('account_banned')->num_rows();
    }

    public function getBanSpecify($id)
    {
        return $this->auth->select('*')->where('id', $id)->where('active', '1')->get('account_banned');
    }

    public function getGmCount($idrealm)
    {
        return $this->auth->select('id')->where('RealmID', $idrealm)->or_where('RealmID', '-1')->get('account_access')->num_rows();
    }

    public function getAccCreated()
    {
        return $this->auth->select('id')->get('account')->num_rows();
    }

    public function getCharOn($multirealm)
    {
        $this->multirealm = $multirealm;

        return $this->multirealm->select('*')->where('online', '1')->get('characters')->num_rows();
    }

    public function getNewsCreated()
    {
        return $this->db->select('id')->get('news')->num_rows();
    }

    public function updateGeneralSettings($project, $timezone, $discord, $realmlist, $theme)
    {
        $this->load->library('config_writer');

        $writer = $this->config_writer->get_instance(APPPATH.'config/blizzcms.php', 'config');
        $writer->write('website_name', $project);
        $writer->write('timezone', $timezone);
        $writer->write('discord_invitation', $discord);
        $writer->write('realmlist', $realmlist);
        $writer->write('theme_name', $theme);
        return true;
    }

    public function updateOptionalSettings($admin, $mod, $recaptcha, $register, $smtphost, $smtpport, $smtpcrypto, $smtpuser, $smtppass, $sender, $sendername)
    {
        $this->load->library('config_writer');

        $writer = $this->config_writer->get_instance(APPPATH.'config/plus.php', 'config');
        $writer->write('recaptcha_sitekey', $recaptcha);
        $writer->write('smtp_host', $smtphost);
        $writer->write('smtp_user', $smtpuser);
        $writer->write('smtp_pass', $smtppass);
        $writer->write('smtp_port', $smtpport);
        $writer->write('smtp_crypto', $smtpcrypto);
        $writer->write('email_settings_sender', $sender);
        $writer->write('email_settings_sender_name', $sendername);
        $writer->write('account_activation_required', $register);
        $writer->write('admin_access_level', $admin);
        $writer->write('mod_access_level', $mod);
        return true;
    }

    public function updateDonateSettings($currency, $mode, $client, $password)
    {
        $this->load->library('config_writer');

        $writer = $this->config_writer->get_instance(APPPATH.'modules/donate/config/donate.php', 'config');
        $writer->write('paypal_currency', $currency);
        $writer->write('paypal_mode', $mode);
        $writer->write('paypal_client', $client);
        $writer->write('paypal_password', $password);
        return true;
    }

    public function updateBugtrackerSettings($textarea)
    {
        $this->load->library('config_writer');

        $writer = $this->config_writer->get_instance(APPPATH.'modules/bugtracker/config/bugtracker.php', 'config');
        $writer->write('textarea', $textarea);
        return true;
    }

    public function insertMenu($name, $url, $icon, $main, $child, $type)
    {
        $data = array(
            'name' => $name,
            'url' => $url,
            'icon' => $icon,
            'main' => $main,
            'child' => $child,
            'type' => $type
        );

        $this->db->insert('menu', $data);
        return true;
    }

    public function updateSpecifyMenu($id, $name, $url, $icon, $main, $child, $type)
    {
        $update = array(
            'name' => $name,
            'url' => $url,
            'icon' => $icon,
            'main' => $main,
            'child' => $child,
            'type' => $type
        );

        $this->db->where('id', $id)->update('menu', $update);
        return true;
    }

    public function delSpecifyMenu($id)
    {
        $this->db->where('id', $id)->delete('menu');
        return true;
    }

    public function getMenuSpecifyName($id)
    {
        return $this->db->select('name')->where('id', $id)->get('menu')->row_array()['name'];
    }

    public function getMenuSpecifyUrl($id)
    {
        return $this->db->select('url')->where('id', $id)->get('menu')->row_array()['url'];
    }

    public function getMenuSpecifyIcon($id)
    {
        return $this->db->select('icon')->where('id', $id)->get('menu')->row_array()['icon'];
    }

    public function getMenuSpecifyMain($id)
    {
        return $this->db->select('main')->where('id', $id)->get('menu')->row_array()['main'];
    }

    public function getMenuSpecifyChild($id)
    {
        return $this->db->select('child')->where('id', $id)->get('menu')->row_array()['child'];
    }

    public function getMenuSpecifyType($id)
    {
        return $this->db->select('type')->where('id', $id)->get('menu')->row_array()['type'];
    }

    public function getMenuSpecifyRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('menu')->num_rows();
    }

    public function getMenu()
    {
        return $this->db->select('*')->get('menu')->result();
    }

    public function getMenuTypeList()
    {
        return $this->db->select('*')->order_by('id', 'ASC')->get('menu_type');
    }

    public function getMenuTypeName($type)
    {
        return $this->db->select('title')->where('id', $type)->get('menu_type')->row_array()['title'];
    }

    public function insertRealm($hostname, $username, $password, $database, $realm_id, $soaphost, $soapuser, $soappass, $soapport)
    {
        $data = array(
            'hostname' => $hostname,
            'username' => $username,
            'password' => $password,
            'char_database' => $database,
            'realmID' => $realm_id,
            'console_hostname' => $soaphost,
            'console_username' => $soapuser,
            'console_password' => $soappass,
            'console_port' => $soapport,
            'emulator' => 'TC'
        );

        $this->db->insert('realms', $data);
        return true;
    }

    public function updateSpecifyRealm($id, $hostname, $username, $password, $database, $realm_id, $soaphost, $soapuser, $soappass, $soapport)
    {
        $update = array(
            'hostname' => $hostname,
            'username' => $username,
            'password' => $password,
            'char_database' => $database,
            'realmID' => $realm_id,
            'console_hostname' => $soaphost,
            'console_username' => $soapuser,
            'console_password' => $soappass,
            'console_port' => $soapport,
            'emulator' => 'TC'
        );

        $this->db->where('id', $id)->update('realms', $update);
        return true;
    }

    public function delSpecifyRealm($id)
    {
        $this->db->where('id', $id)->delete('realms');
        return true;
    }

    public function getAllRealms()
    {
        $this->db->from('realms');
        return $this->db->count_all_results();
    }

    public function RealmsList()
    {
        return $this->db->select('*')->limit($this->_pageNumber, $this->_offset)->get('realms')->result();
    }

    public function insertSlide($title, $description, $type, $route)
    {
        $data = array(
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'route' => $route
        );

        $this->db->insert('slides', $data);
        return true;
    }

    public function updateSpecifySlide($id, $title, $description, $type, $route)
    {
        $update = array(
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'route' => $route
        );

        $this->db->where('id', $id)->update('slides', $update);
        return true;
    }

    public function delSpecifySlide($id)
    {
        $this->db->where('id', $id)->delete('slides');
        return true;
    }

    public function getAllSlides()
    {
        $this->db->from('slides');
        return $this->db->count_all_results();
    }

    public function SlidesList()
    {
        return $this->db->select('*')->limit($this->_pageNumber, $this->_offset)->get('slides')->result();
    }

    public function insertNews($title, $description, $image)
    {
        $date = $this->wowgeneral->getTimestamp();

        $data = array(
            'title' => $title,
            'image' => $image,
            'description' => $description,
            'date' => $date,
        );

        $this->db->insert('news', $data);

        redirect(base_url('admin/news'),'refresh');
    }

    public function updateSpecifyNews($id, $title, $description, $image)
    {
        $unlink = $this->getFileNameImage($id);
        unlink('./assets/images/news/'.$unlink);

        $date = $this->wowgeneral->getTimestamp();

        $update = array(
            'title' => $title,
            'image' => $image,
            'description' => $description,
            'date' => $date
        );

        $this->db->where('id', $id)->update('news', $update);

        redirect(base_url('admin/news'),'refresh');
    }

    public function delSpecifyNew($id)
    {
        $this->db->where('id', $id)->delete('news');
        $this->db->where('id_new', $id)->delete('news_top');
        return true;
    }

    public function getAllNews()
    {
        $this->db->from('news');
        return $this->db->count_all_results();
    }

    public function NewsList()
    {
        return $this->db->select('*')->limit($this->_pageNumber, $this->_offset)->get('news')->result();
    }

    public function getNewsSpecifyRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('news')->num_rows();
    }

    public function getNewIDperDate($date)
    {
        return $this->db->select('id')->where('date', $date)->get('news')->row('id');
    }

    public function getFileNameImage($id)
    {
        return $this->db->select('image')->where('id', $id)->get('news')->row_array()['image'];
    }

    public function getNewsSpecifyName($id)
    {
        return $this->db->select('title')->where('id', $id)->get('news')->row_array()['title'];
    }

    public function getNewsSpecifyDesc($id)
    {
        return $this->db->select('description')->where('id', $id)->get('news')->row_array()['description'];
    }

    public function insertChangelog($title, $description)
    {
        $date = $this->wowgeneral->getTimestamp();

        $data = array(
            'title' => $title,
            'description' => $description,
            'date' => $date,
        );

        $this->db->insert('changelogs', $data);
        return true;
    }

    public function updateSpecifyChangelog($id, $title, $description)
    {
        $date = $this->wowgeneral->getTimestamp();

        $update = array(
            'title' => $title,
            'description' => $description,
            'date' => $date
        );

        $this->db->where('id', $id)->update('changelogs', $update);
        return true;
    }

    public function delChangelog($id)
    {
        $this->db->where('id', $id)->delete('changelogs');
        return true;
    }

    public function getAllChangelogs()
    {
        $this->db->from('changelogs');
        return $this->db->count_all_results();
    }

    public function ChangelogsList()
    {
        return $this->db->select('*')->limit($this->_pageNumber, $this->_offset)->get('changelogs')->result();
    }

    public function getChangelogsCreated()
    {
        return $this->db->select('id')->get('changelogs')->num_rows();
    }

    public function getChangelogSpecifyRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('changelogs')->num_rows();
    }

    public function getChangelogSpecifyName($id)
    {
        return $this->db->select('title')->where('id', $id)->get('changelogs')->row('title');
    }

    public function getChangelogSpecifyDesc($id)
    {
        return $this->db->select('description')->where('id', $id)->get('changelogs')->row_array()['description'];
    }

    public function insertPage($title, $uri, $description)
    {
        $date = $this->wowgeneral->getTimestamp();
        $rand = rand(1, 15);

        if($this->pagecheckUri($uri) == TRUE) {
            $new_uri = $uri."-".$rand;

            $data = array(
                'title' => $title,
                'uri_friendly' => $new_uri,
                'description' => $description,
                'date' => $date
            );

            $this->db->insert('pages', $data);
            return true;
        } 
        else
            $data1 = array(
                'title' => $title,
                'uri_friendly' => $uri,
                'description' => $description,
                'date' => $date
            );

            $this->db->insert('pages', $data1);
            return true;
    }

    public function updateSpecifyPage($id, $title, $uri, $description)
    {
        $date = $this->wowgeneral->getTimestamp();

        $update = array(
            'title' => $title,
            'uri' => $uri,
            'description' => $description,
            'date' => $date
        );

        $this->db->where('id', $id)->update('pages', $update);
        return true;
    }

    public function delPage($id)
    {
        $this->db->where('id', $id)->delete('pages');
        return true;
    }

    public function getAllPages()
    {
        $this->db->from('pages');
        return $this->db->count_all_results();
    }

    public function PagesList()
    {
        return $this->db->select('*')->limit($this->_pageNumber, $this->_offset)->get('pages')->result();
    }

    public function getPagesSpecifyRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('pages')->num_rows();
    }

    public function pagecheckUri($uri)
    {
        $qq = $this->db->select('uri_friendly')->where('uri_friendly', $uri)->get('pages')->row('uri_friendly');

        if($qq == $uri) {
            return true;
        } else {
            return false;
        }
    }

    public function getPagesSpecifyName($id)
    {
        return $this->db->select('title')->where('id', $id)->get('pages')->row('title');
    }

    public function getPagesSpecifyDesc($id)
    {
        return $this->db->select('description')->where('id', $id)->get('pages')->row_array()['description'];
    }

    public function insertTopsite($name, $url, $time, $points, $image)
    {
        $data = array(
            'name' => $name,
            'url' => $url,
            'time' => $time,
            'points' => $points,
            'image' => $image
        );

        $this->db->insert('votes', $data);
        return true;
    }

    public function updateSpecifyTopsite($id, $name, $url, $time, $points, $image)
    {
        $update = array(
            'name' => $name,
            'url' => $url,
            'time' => $time,
            'points' => $points,
            'image' => $image
        );

        $this->db->where('id', $id)->update('votes', $update);
        return true;
    }

    public function delTopsite($id)
    {
        $this->db->where('id', $id)->delete('votes');
        return true;
    }

    public function getAllTopsites()
    {
        $this->db->from('votes');
        return $this->db->count_all_results();
    }

    public function TopsitesList()
    {
        return $this->db->select('*')->limit($this->_pageNumber, $this->_offset)->get('votes')->result();
    }

    public function getTopsitesSpecifyRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('votes')->num_rows();
    }

    public function getTopsiteSpecifyName($id)
    {
        return $this->db->select('name')->where('id', $id)->get('votes')->row('name');
    }

    public function getTopsiteSpecifyURL($id)
    {
        return $this->db->select('url')->where('id', $id)->get('votes')->row('url');
    }

    public function getTopsiteSpecifyTime($id)
    {
        return $this->db->select('time')->where('id', $id)->get('votes')->row('time');
    }

    public function getTopsiteSpecifyPoints($id)
    {
        return $this->db->select('points')->where('id', $id)->get('votes')->row('points');
    }

    public function getTopsiteSpecifyImage($id)
    {
        return $this->db->select('image')->where('id', $id)->get('votes')->row('image');
    }

    public function getModules()
    {
        return $this->db->select('*')->get('modules')->result();
    }

    public function enableSpecifyModule($id)
    {
        $update = array(
            'status' => '1'
        );

        $this->db->where('id', $id)->update('modules', $update);
        return true;
    }

    public function disableSpecifyModule($id)
    {
        $update = array(
            'status' => '0'
        );

        $this->db->where('id', $id)->update('modules', $update);
        return true;
    }

    public function insertGroup($name)
    {
        $data = array(
            'name' => $name,
        );

        $this->db->insert('store_categories', $data);
        return true;
    }

    public function updateSpecifyGroup($idlink, $group)
    {
        $update = array(
            'name' => $group,
        );

        $this->db->where('id', $idlink)->update('store_categories', $update);
        return true;
    }

    public function deleteGroup($id)
    {
        $this->db->where('id', $id)->delete('store_categories');
        return true;
    }

    public function getShopGroupList()
    {
        return $this->db->select('*')->order_by('id', 'ASC')->get('store_categories');
    }

    public function getCategoryStore()
    {
        return $this->db->select('*')->get('store_categories');
    }

    public function getGroupSpecifyRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('store_categories')->num_rows();
    }

    public function getGroupName($id)
    {
        return $this->db->select('name')->where('id', $id)->get('store_categories')->row_array()['name'];
    }

    public function insertItem($name, $category, $type, $pricedp, $pricevp, $itemid, $icon, $image)
    {
        if ($pricevp == '0' && $pricedp == '0')
        redirect(base_url('admin/store'),'refresh');

        if ($pricedp == '0')
            $pricedp = NULL;

        if ($pricevp == '0')
            $pricevp = NULL;

        $data = array(
            'name' => $name,
            'category' => $category,
            'type' => $type,
            'price_dp' => $pricedp,
            'price_vp' => $pricevp,
            'itemid' => $itemid,
            'icon' => $icon,
            'image' => $image
        );

        $this->db->insert('store_items', $data);
        return true;
    }

    public function updateSpecifyItem($id, $name, $category, $type, $pricedp, $pricevp, $itemid, $icon, $image)
    {
        $update = array(
            'name' => $name,
            'category' => $category,
            'type' => $type,
            'price_dp' => $pricedp,
            'price_vp' => $pricevp,
            'itemid' => $itemid,
            'icon' => $icon,
            'image' => $image
        );

        $this->db->where('id', $id)->update('store_items', $update);
        return true;
    }

    public function delShopItm($id)
    {
        $this->db->where('id', $id)->delete('store_items');
        $this->db->where('id_store', $id)->delete('store_top');
        return true;
    }

    public function getShopAll()
    {
        return $this->db->select('*')->order_by('id', 'ASC')->get('store_items');
    }

    public function getItemSpecifyRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('store_items')->num_rows();
    }

    public function getItemSpecifyName($id)
    {
        return $this->db->select('name')->where('id', $id)->get('store_items')->row('name');
    }

    public function getItemSpecifyDpPrice($id)
    {
        return $this->db->select('price_dp')->where('id', $id)->get('store_items')->row_array()['price_dp'];
    }

    public function getItemSpecifyVpPrice($id)
    {
        return $this->db->select('price_vp')->where('id', $id)->get('store_items')->row_array()['price_vp'];
    }

    public function getItemSpecifyId($id)
    {
        return $this->db->select('itemid')->where('id', $id)->get('store_items')->row_array()['itemid'];
    }

    public function getItemSpecifyIcon($id)
    {
        return $this->db->select('icon')->where('id', $id)->get('store_items')->row_array()['icon'];
    }

    public function getItemSpecifyImg($id)
    {
        return $this->db->select('image')->where('id', $id)->get('store_items')->row_array()['image'];
    }

    public function getItemSpecifyGroup($id)
    {
        return $this->db->select('category')->where('id', $id)->get('store_items')->row_array()['category'];
    }

    public function getItemSpecifyType($id)
    {
        return $this->db->select('type')->where('id', $id)->get('store_items')->row_array()['type'];
    }

    public function insertForum($name, $description, $icon, $type, $category)
    {
        $data = array(
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'icon' => $icon,
            'type' => $type
        );

        $this->db->insert('forum', $data);
        return true;
    }

    public function updateSpecifyForum($id, $name, $description, $icon, $type, $category)
    {
        $update = array(
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'icon' => $icon,
            'type' => $type
        );

        $this->db->where('id', $id)->update('forum', $update);
        return true;
    }

    public function deleteForum($id)
    {
        $this->db->where('id', $id)->delete('forum');
        return true;
    }

    public function getForumForumList()
    {
        return $this->db->select('*')->order_by('id', 'ASC')->get('forum');
    }

    public function getSpecifyForumRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('forum')->num_rows();
    }

    public function getSpecifyForumName($id)
    {
        return $this->db->select('name')->where('id', $id)->get('forum')->row_array()['name'];
    }

    public function getSpecifyForumDesc($id)
    {
        return $this->db->select('description')->where('id', $id)->get('forum')->row_array()['description'];
    }

    public function getSpecifyForumIcon($id)
    {
        return $this->db->select('icon')->where('id', $id)->get('forum')->row_array()['icon'];
    }

    public function getSpecifyForumCategory($id)
    {
        return $this->db->select('category')->where('id', $id)->get('forum')->row_array()['category'];
    }

    public function getSpecifyForumType($id)
    {
        return $this->db->select('type')->where('id', $id)->get('forum')->row_array()['type'];
    }

    public function insertForumCategory($category)
    {
        $data = array(
            'categoryName' => $category
        );

        $this->db->insert('forum_category', $data);
        return true;
    }

    public function updateForumCategory($id, $category)
    {
        $update = array(
            'categoryName' => $category
        );

        $this->db->where('id', $id)->update('forum_category', $update);
        return true;
    }

    public function deleteForumCategory($id)
    {
        $this->db->where('id', $id)->delete('forum_category');
        return true;
    }

    public function getForumCategoryList()
    {
        return $this->db->select('*')->order_by('id', 'ASC')->get('forum_category');
    }

    public function getSpecifyForumCategoryRows($id)
    {
        return $this->db->select('*')->where('id', $id)->get('forum')->num_rows();
    }

    public function getForumCategoryName($id)
    {
        return $this->db->select('categoryName')->where('id', $id)->get('forum_category')->row_array()['categoryName'];
    }
}
