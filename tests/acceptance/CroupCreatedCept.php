<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('can\'t create group if it\'s already created');

// GF group already created
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','elen');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/elen/');
$I->moveMouseOver("//a[@title='My Account']");
$I->see('Group', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Group']");
$I->dontsee('Create My Group', '.ab-item');
$I->see('Group Created', '.ab-item');
$I->moveMouseOver("//a[@title='My Account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");
$I->wait(3);
//--
