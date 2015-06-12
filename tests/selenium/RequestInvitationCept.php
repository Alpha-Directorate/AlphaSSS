<?php 
$I = new SeleniumTester($scenario);

$elen = $I->haveFriend('elen');
$elen->does(function(SeleniumTester $I) {
	$I->amOnPage('/');
	$I->resizeWindow(1024, 1024);
	$I->see('Login','.button');
	$I->click("//a[@class='button' and text()='Login']");
	$I->seeCurrentUrlEquals('/wp/wp-login.php');
	$I->fillField('log','elen');
	$I->fillField('pwd','funkadelicbro87');
	$I->click('Log In');
	$I->seeCurrentUrlEquals('/browse/elen/');
});

$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','tanya');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/register-pre-member/');
$I->see('Your Invitation Code','h1');
$I->see('Browse','li');
$I->click("//a[text()='Browse']");
$I->wait(3);
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/span[@class='activity']/div[@class='member-online']");
$I->see('Request Invitation', '#' . md5('elen'));
$I->click('Request Invitation', '#' . md5('elen'));
$I->wait(3);
$elen = $I->haveFriend('elen');
$elen->does(function(SeleniumTester $I) {
	$I->see('Deliver the Code');
	$I->click('//button[@id="deliver-invitation-code" and text()="Deliver the Code"]');
});
$I->wait(3);
$I->see('elen has sent you and invitation code:');