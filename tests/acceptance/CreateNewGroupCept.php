<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Group creation for GF');
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
$I->see('Create My Group', '.ab-item');