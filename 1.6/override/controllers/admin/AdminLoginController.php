<?php
class AdminLoginController extends AdminLoginControllerCore
{
	// override prestashop security 
	// https://gist.github.com/xBorderie/15c48651e5c91ba0141f/6fcffc3bc37523235fa92a1a5276008ed3fed45c
	public function processForgot()
	{
		if (_PS_MODE_DEMO_)
			$this->errors[] = Tools::displayError('This functionality has been disabled.');
		elseif (!($email = trim(Tools::getValue('email_forgot'))))
			$this->errors[] = Tools::displayError('Email is empty.');
		elseif (!Validate::isEmail($email))
			$this->errors[] = Tools::displayError('Invalid email address.');
		else
		{
			$employee = new Employee();
			if (!$employee->getByEmail($email) || !$employee)
				$this->errors[] = Tools::displayError('This account does not exist.');
			elseif ((strtotime($employee->last_passwd_gen.'+'.Configuration::get('PS_PASSWD_TIME_BACK').' minutes') - time()) > 0)
				$this->errors[] = sprintf(
					Tools::displayError('You can regenerate your password only every %d minute(s)'),
					Configuration::get('PS_PASSWD_TIME_BACK')
				);
		}

		if (!count($this->errors))
		{	
			$pwd = Tools::passwdGen(10, 'RANDOM');
			$employee->passwd = Tools::encrypt($pwd);
			$employee->last_passwd_gen = date('Y-m-d H:i:s', time());

			$params = array(
				'{email}' => $employee->email,
				'{lastname}' => $employee->lastname,
				'{firstname}' => $employee->firstname,
				'{passwd}' => $pwd
			);
						
			if (Mail::Send($employee->id_lang, 'employee_password', Mail::l('Your new password', $employee->id_lang), $params, $employee->email, $employee->firstname.' '.$employee->lastname))
			{
				// Update employee only if the mail can be sent
				Shop::setContext(Shop::CONTEXT_SHOP, (int)min($employee->getAssociatedShops()));

				$result = $employee->update();
				if (!$result)
					$this->errors[] = Tools::displayError('An error occurred while attempting to change your password.');
				else
					die(Tools::jsonEncode(array(
						'hasErrors' => false,
						'confirm' => $this->l('Your password has been emailed to you.', 'AdminTab', false, false)
					)));
			}
			else
				die(Tools::jsonEncode(array(
					'hasErrors' => true,
					'errors' => array(Tools::displayError('An error occurred while attempting to change your password.'))
				)));
		
		}
		elseif (Tools::isSubmit('ajax'))
			die(Tools::jsonEncode(array('hasErrors' => true, 'errors' => $this->errors)));
	}
}
