<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

 /**
  * Reference implementation for a basic acceptance cest
  */
class BasicAcceptanceCest
{
    /**
     * Code to be executed in order to prepare the test scenario
     *
     * @param AcceptanceTester $I implementation of acceptance tester class
     */
    public function before(AcceptanceTester $I)
    {
    }

    /**
     * Basic search test case
     *
     * @param AcceptanceTester $I implementation of acceptance tester class
     */
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->fillField('searchparam', 'kite');
        $I->click('button[title="Suchen"]');
        $I->see('25 Treffer für "kite"');
    }
}
