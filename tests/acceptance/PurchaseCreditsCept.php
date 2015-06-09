<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('purchase AlphaSSS credits');
$I->amOnPage('/');
$I->see('Register','li');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->seeElement('#wp-admin-bar-user-credits');
$I->click('#wp-admin-bar-user-credits a');
$I->seeCurrentUrlEquals('/purchase-credits/');
$I->seeElement('#credit-selection');
$I->seeElement('#purchase-credits', ['disabled' => true]);
$I->selectOption('#credit-selection', '1000 Credits ($10 USD)');
$I->seeOptionIsSelected('#credit-selection', '1000 Credits ($10 USD)');
$I->dontSeeElement('#purchase-credits', ['disabled' => true]);
$I->seeElement('#purchase-credits', ['disabled' => false]);
$I->selectOption('#credit-selection', 'Any amount you choose:');
$I->seeElement('#purchase-credits', ['disabled' => true]);
?>