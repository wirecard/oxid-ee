<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use Wirecard\Oxid\Extend\Core\Email;

class EmailTest  extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @dataProvider testSendSupportEmailProvider
     */
    public function testSendSupportEmail($aEmailData)
    {
        $oSmartyMock = $this->getMock("Smarty", array("fetch", "assign"));
        $oSmartyMock->expects($this->any())->method("fetch")->will($this->returnValue(true));

        $oEmail = $this->getMock(Email::class, array("_sendMail", "_getSmarty", "send"));
        $oEmail->expects($this->any())->method("_getSmarty")->will($this->returnValue($oSmartyMock));

        $oEmail->sendSupportEmail($aEmailData);

        $aViewData = $oEmail->getViewData();
        $sSubject = $oEmail->getSubject();
        $sFrom = $oEmail->getFrom();
        $sReplyTo = $oEmail->getReplyTo()[0][0];

        $this->assertEquals($aViewData['emailData']['from'], $aEmailData['from']);
        $this->assertEquals($sSubject, $aEmailData['subject']);
        $this->assertEquals($sFrom, $aEmailData['from']);

        if ($aEmailData['replyTo']){
            $this->assertEquals($sReplyTo, $aEmailData['replyTo']);
        } else {
            $this->assertEquals($sReplyTo, $aEmailData['from']);
        }

    }

    public function testSendSupportEmailProvider()
    {
        $aEmailDataWithoutReplyTo = [
            'body' => 'body test',
            'from' => 'test@from.test',
            'modules' => [],
            'module' => null,
            'shopVersion' => 'test version',
            'shopEdition' => 'test edition',
            'phpVersion' => 'test php version',
            'system' => 'test system',
            'subject' => 'test subject',
            'recipient' => 'test@recipient.test',
            'payments' => [],
        ];

        $aEmailDataComplete = $aEmailDataWithoutReplyTo;
        $aEmailDataComplete['replyTo'] = 'test@reply.test';

        return [
            'withoutReplyTo' => [$aEmailDataWithoutReplyTo],
            'complete' => [$aEmailDataComplete]
        ];
    }
}
