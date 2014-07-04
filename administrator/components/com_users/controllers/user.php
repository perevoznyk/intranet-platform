<?php
/**
 * @version		$Id: user.php 3024 2011-10-09 01:44:30Z johanjanssens $
 * @category	Nooku
 * @package		Nooku_Server
 * @subpackage	Users
 * @copyright	Copyright (C) 2011 - 2012 Timble CVBA and Contributors. (http://www.timble.net).
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://www.nooku.org
 */

/**
 * User Controller Class
 *
 * @author      Gergo Erdosi <http://nooku.assembla.com/profile/gergoerdosi>
 * @category	Nooku
 * @package		Nooku_Server
 * @subpackage	Users
 */
class ComUsersControllerUser extends ComDefaultControllerDefault
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->registerCallback('after.add', array($this, 'notify'));
        
        //Lock the referrer to prevent it from being overridden for read requests
        if ($this->isDispatched() && KRequest::type() == 'HTTP') 
        {
		    if($this->isEditable()) {
		        $this->registerCallback('after.logout' , array($this, 'lockReferrer'));
		    }
        }
    }

    protected function _actionDelete(KCommandContext $context)
    {
        $rowset = parent::_actionDelete($context);

        $list = $this->getService('com://admin/users.model.sessions')
            ->set('username', $rowset->username)
            ->getList()
            ->delete();

        return $rowset;
    }

    protected function _actionLogin(KCommandContext $context)
    {
        $credentials['username'] = KRequest::get('post.username', 'string');
        $credentials['password'] = KRequest::get('post.password', 'raw');

        $result = JFactory::getApplication()->login($credentials);

        if(JError::isError($result))
        {
            $this->_redirect_type    = 'error';
            $this->_redirect_message =  $result->getError();
        }

        $this->_redirect = KRequest::referrer();
    }

    protected function _actionLogout(KCommandContext $context)
    {
        $users = $this->getModel()->getList();
        					
	    if(count($users)) 
	    {
	        foreach($users as $user)
	        {
	            $clients = array(0, 1); //Force logout from site and administrator
	            $result = JFactory::getApplication()
	                            ->logout($user->id, array('clientid' => $clients));

                if(JError::isError($result))
                {
                    $this->_redirect_type    = 'error';
                    $this->_redirect_message =  $result->getError();
                }
	        }
		} 
	
        $this->_redirect = KRequest::referrer();
    }

    public function notify(KCommandContext $context)
    {
        if($context->result->status == KDatabase::STATUS_CREATED)
        {
            $application = JFactory::getApplication();

            // Send e-mail to the user.
            $mail_from_email    = $application->getCfg('mailfrom');
            $mail_from_name     = $application->getCfg('fromname');
            $mail_site_name     = $application->getCfg('sitename');

            if($mail_from_email == '' || $mail_from_name == '')
            {
                $user = JFactory::getUser();

                $mail_from_email    = $user->email;
                $mail_from_name     = $user->name;
            }

            $subject = JText::_('NEW_USER_MESSAGE_SUBJECT');
            $message = JText::sprintf('NEW_USER_MESSAGE', $context->result->name, $mail_site_name, KRequest::root(),
                $context->result->username, $context->result->password);

            JUtility::sendMail($mail_from_email, $mail_from_name, $context->result->email, $subject, $message);
        }
    }
}