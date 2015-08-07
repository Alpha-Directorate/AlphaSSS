<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('purchase AlphaSSS credits');
$I->amOnPage('/');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->seeElement('#credits-icon');
$I->moveMouseOver("#credits-icon");
$I->click('Buy Credits');
$I->seeCurrentUrlEquals('/purchase-credits/');
$I->seeElement('#credit-selection');
$I->seeElement('#purchase-credits', ['disabled' => true]);
$I->selectOption('#credit-selection', '10.00 Credits ($10.00 USD)');
$I->seeOptionIsSelected('#credit-selection', '10.00 Credits ($10.00 USD)');
$I->dontSeeElement('#purchase-credits', ['disabled' => true]);
$I->seeElement('#purchase-credits', ['disabled' => false]);
$I->selectOption('#credit-selection', 'Any amount you choose:');
$I->seeElement('#purchase-credits', ['disabled' => true]);
$I->selectOption('#credit-selection', '10.00 Credits ($10.00 USD)');
$I->seeElement('#purchase-credits', ['disabled' => false]);
/**
* $I->click('#purchase-credits');
* $I->seeCurrentUrlEquals('/pay-with-bitpay/');
* $I->see(0, '#credit-balance');
* $I->switchToIFrame("bitpay_checkout");
* $I->see('Purchase 1000 Credits ($10.00 USD)');
*/
?>