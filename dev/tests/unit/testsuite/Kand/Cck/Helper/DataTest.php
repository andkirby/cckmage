<?php
/**
 * Test of class Kand_Cck_Helper_Data
 *
 * @category    Kand
 * @package     Kand_Cck
 */
class Kand_Cck_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test class name
     *
     * @var string
     */
    protected $_className = 'Kand_Cck_Helper_Data';

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf($this->_className, $this->getMock($this->_className));
    }

    /**
     * Test collecting texts in HTML
     */
    public function testCollectTexts()
    {
        $test = $this->getMock($this->_className, array('collectTexts'));
        //@startSkipCommitHooks
        $html = <<<HTML
<div>
    <h1>Some Title</h1>
    <span>Span Text</span>
    <a href="#mylink">My pretty nice <em>link</em></a>
    <p>Some long paragraph
    with a <a>link</a></p>
</div>
HTML;
        //@finishSkipCommitHooks

        $result = $test->collectTexts(
            $html
        );

        $this->assertEquals(
            array(
                'Some Title',
                'Span Text',
                'My pretty nice <em>link</em>',
                'Some long paragraph with a <a>link</a>',
            ),
            $result
        );
    }
}
