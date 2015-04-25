<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Making micro donations by member');
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','nadya');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/nadya/');
$I->click("//a[@id='user-groups' and text()[contains(.,'Neighborhood')]]");
$I->seeCurrentUrlEquals('/browse/nadya/groups/');
$I->see('The Neighborhood','#groups-my-groups');
$I->click('Sex Lovers United');
$I->seeCurrentUrlEquals('/groups/sex-lovers-united/');
$I->see('Homies','#members');
$I->see('micro-Donate');
