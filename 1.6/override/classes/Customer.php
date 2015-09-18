<?php
class Customer extends CustomerCore
{
	// override prestashop security 
	// https://gist.github.com/xBorderie/15c48651e5c91ba0141f/6fcffc3bc37523235fa92a1a5276008ed3fed45c
	public function transformToCustomer($id_lang, $password = null)
	{
		if (!$this->isGuest())
			return false;
		if (empty($password))
			$password = Tools::passwdGen(8, 'RANDOM');
		if (!Validate::isPasswd($password))
			return false;

		$this->is_guest = 0;
		$this->passwd = Tools::encrypt($password);
		$this->cleanGroups();
		$this->addGroups(array(Configuration::get('PS_CUSTOMER_GROUP'))); // add default customer group
		if ($this->update())
		{
			$vars = array(
				'{firstname}' => $this->firstname,
				'{lastname}' => $this->lastname,
				'{email}' => $this->email,
				'{passwd}' => $password
			);

			Mail::Send(
				(int)$id_lang,
				'guest_to_customer',
				Mail::l('Your guest account has been transformed into a customer account', (int)$id_lang),
				$vars,
				$this->email,
				$this->firstname.' '.$this->lastname,
				null,
				null,
				null,
				null,
				_PS_MAIL_DIR_,
				false,
				(int)$this->id_shop
			);
			return true;
		}
		return false;
	}
}
