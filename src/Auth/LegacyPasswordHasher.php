<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 22/03/2019
 * Time: 13:02
 */

namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;

class LegacyPasswordHasher extends AbstractPasswordHasher
{

	public function hash($password)
	{
		return md5($password);
	}

	public function check($password, $hashedPassword) : bool
	{
		return md5($password) === $hashedPassword;
	}
}
// EoF
