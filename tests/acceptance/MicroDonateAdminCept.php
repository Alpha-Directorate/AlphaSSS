<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check micro donations for group admin');
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->click("//a[@id='user-groups' and text()[contains(.,'Neighborhood')]]");
$I->seeCurrentUrlEquals('/browse/saybb/groups/');
$I->see('The Neighborhood','#groups-my-groups');
$I->click('Sex Lovers United');
$I->seeCurrentUrlEquals('/groups/sex-lovers-united/');
$I->see('Homies','#members');
$I->dontSee('micro-Donate');
