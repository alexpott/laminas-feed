<?php

/**
 * @see       https://github.com/laminas/laminas-feed for the canonical source repository
 * @copyright https://github.com/laminas/laminas-feed/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-feed/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Feed\Writer\Renderer\Entry;

use Laminas\Feed\Reader;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;

/**
 * @category   Laminas
 * @package    Laminas_Feed
 * @subpackage UnitTests
 * @group      Laminas_Feed
 * @group      Laminas_Feed_Writer
 */
class RssTest extends \PHPUnit_Framework_TestCase
{

    protected $_validWriter = null;
    protected $_validEntry = null;

    public function setUp()
    {
        $this->_validWriter = new Writer\Feed;

        $this->_validWriter->setType('rss');

        $this->_validWriter->setTitle('This is a test feed.');
        $this->_validWriter->setDescription('This is a test description.');
        $this->_validWriter->setLink('http://www.example.com');
        $this->_validEntry = $this->_validWriter->createEntry();
        $this->_validEntry->setTitle('This is a test entry.');
        $this->_validEntry->setDescription('This is a test entry description.');
        $this->_validEntry->setLink('http://www.example.com/1');
        $this->_validWriter->addEntry($this->_validEntry);
    }

    public function tearDown()
    {
        $this->_validWriter = null;
        $this->_validEntry  = null;
    }

    public function testRenderMethodRunsMinimalWriterContainerProperlyBeforeICheckAtomCompliance()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $renderer->render();
    }

    public function testEntryEncodingHasBeenSet()
    {
        $this->_validWriter->setEncoding('iso-8859-1');
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('iso-8859-1', $entry->getEncoding());
    }

    public function testEntryEncodingDefaultIsUsedIfEncodingNotSetByHand()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('UTF-8', $entry->getEncoding());
    }

    public function testEntryTitleHasBeenSet()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('This is a test entry.', $entry->getTitle());
    }

    /**
     * @expectedException Laminas\Feed\Writer\Exception\ExceptionInterface
     */
    public function testEntryTitleIfMissingThrowsExceptionIfDescriptionAlsoMissing()
    {
        $atomFeed = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->remove('title');
        $this->_validEntry->remove('description');
        $atomFeed->render();
    }

    public function testEntryTitleCharDataEncoding()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setTitle('<>&\'"áéíóú');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('<>&\'"áéíóú', $entry->getTitle());
    }

    public function testEntrySummaryDescriptionHasBeenSet()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('This is a test entry description.', $entry->getDescription());
    }

    /**
     * @expectedException Laminas\Feed\Writer\Exception\ExceptionInterface
     */
    public function testEntryDescriptionIfMissingThrowsExceptionIfAlsoNoTitle()
    {
        $atomFeed = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->remove('description');
        $this->_validEntry->remove('title');
        $atomFeed->render();
    }

    public function testEntryDescriptionCharDataEncoding()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setDescription('<>&\'"áéíóú');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('<>&\'"áéíóú', $entry->getDescription());
    }

    public function testEntryContentHasBeenSet()
    {
        $this->_validEntry->setContent('This is test entry content.');
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('This is test entry content.', $entry->getContent());
    }

    public function testEntryContentCharDataEncoding()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setContent('<>&\'"áéíóú');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('<>&\'"áéíóú', $entry->getContent());
    }

    public function testEntryUpdatedDateHasBeenSet()
    {
        $this->_validEntry->setDateModified(1234567890);
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals(1234567890, $entry->getDateModified()->getTimestamp());
    }

    public function testEntryPublishedDateHasBeenSet()
    {
        $this->_validEntry->setDateCreated(1234567000);
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals(1234567000, $entry->getDateCreated()->getTimestamp());
    }

    public function testEntryIncludesLinkToHtmlVersionOfFeed()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getLink());
    }

    public function testEntryHoldsAnyAuthorAdded()
    {
        $this->_validEntry->addAuthor(array('name' => 'Jane',
                                            'email'=> 'jane@example.com',
                                            'uri'  => 'http://www.example.com/jane'));
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $author   = $entry->getAuthor();
        $this->assertEquals(array('name'=> 'Jane'), $entry->getAuthor());
    }

    public function testEntryAuthorCharDataEncoding()
    {
        $this->_validEntry->addAuthor(array('name' => '<>&\'"áéíóú',
                                            'email'=> 'jane@example.com',
                                            'uri'  => 'http://www.example.com/jane'));
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $author   = $entry->getAuthor();
        $this->assertEquals(array('name'=> '<>&\'"áéíóú'), $entry->getAuthor());
    }

    public function testEntryHoldsAnyEnclosureAdded()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setEnclosure(array(
                                              'type'   => 'audio/mpeg',
                                              'length' => '1337',
                                              'uri'    => 'http://example.com/audio.mp3'
                                         ));
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $enc   = $entry->getEnclosure();
        $this->assertEquals('audio/mpeg', $enc->type);
        $this->assertEquals('1337', $enc->length);
        $this->assertEquals('http://example.com/audio.mp3', $enc->url);
    }

    /**
     * @expectedException Laminas\Feed\Writer\Exception\ExceptionInterface
     */
    public function testAddsEnclosureThrowsExceptionOnMissingType()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setEnclosure(array(
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => '1337'
                                         ));
        $renderer->render();
    }

    /**
     * @expectedException Laminas\Feed\Writer\Exception\ExceptionInterface
     */
    public function testAddsEnclosureThrowsExceptionOnMissingLength()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setEnclosure(array(
                                              'type' => 'audio/mpeg',
                                              'uri'  => 'http://example.com/audio.mp3'
                                         ));
        $renderer->render();
    }

    /**
     * @expectedException Laminas\Feed\Writer\Exception\ExceptionInterface
     */
    public function testAddsEnclosureThrowsExceptionOnNonNumericLength()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setEnclosure(array(
                                              'type'   => 'audio/mpeg',
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => 'abc'
                                         ));
        $renderer->render();
    }

    /**
     * @expectedException Laminas\Feed\Writer\Exception\ExceptionInterface
     */
    public function testAddsEnclosureThrowsExceptionOnNegativeLength()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setEnclosure(array(
                                              'type'   => 'audio/mpeg',
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => -23
                                         ));
        $renderer->render();
    }

    public function testEntryIdHasBeenSet()
    {
        $this->_validEntry->setId('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6');
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6', $entry->getId());
    }

    public function testEntryIdHasBeenSetWithPermaLinkAsFalseWhenNotUri()
    {
        $this->markTestIncomplete('Untest due to LaminasR potential bug');
    }

    public function testEntryIdDefaultIsUsedIfNotSetByHand()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals($entry->getLink(), $entry->getId());
    }

    public function testCommentLinkRendered()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setCommentLink('http://www.example.com/id/1');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/id/1', $entry->getCommentLink());
    }

    public function testCommentCountRendered()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setCommentCount(22);
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals(22, $entry->getCommentCount());
    }

    public function testCommentFeedLinksRendered()
    {
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $this->_validEntry->setCommentFeedLinks(array(
                                                     array('uri' => 'http://www.example.com/atom/id/1',
                                                           'type'=> 'atom'),
                                                     array('uri' => 'http://www.example.com/rss/id/1',
                                                           'type'=> 'rss'),
                                                ));
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        // Skipped assertion is because RSS has no facility to show Atom feeds without an extension
        $this->assertEquals('http://www.example.com/rss/id/1', $entry->getCommentFeedLink('rss'));
        //$this->assertEquals('http://www.example.com/atom/id/1', $entry->getCommentFeedLink('atom'));
    }

    public function testCategoriesCanBeSet()
    {
        $this->_validEntry->addCategories(array(
                                               array('term'   => 'cat_dog',
                                                     'label'  => 'Cats & Dogs',
                                                     'scheme' => 'http://example.com/schema1'),
                                               array('term'=> 'cat_dog2')
                                          ));
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $expected = array(
            array('term'   => 'cat_dog',
                  'label'  => 'cat_dog',
                  'scheme' => 'http://example.com/schema1'),
            array('term'   => 'cat_dog2',
                  'label'  => 'cat_dog2',
                  'scheme' => null)
        );
        $this->assertEquals($expected, (array)$entry->getCategories());
    }

    /**
     * @group LaminasWCHARDATA01
     */
    public function testCategoriesCharDataEncoding()
    {
        $this->_validEntry->addCategories(array(
                                               array('term'   => '<>&\'"áéíóú',
                                                     'label'  => 'Cats & Dogs',
                                                     'scheme' => 'http://example.com/schema1'),
                                               array('term'=> 'cat_dog2')
                                          ));
        $renderer = new Renderer\Feed\Rss($this->_validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $expected = array(
            array('term'   => '<>&\'"áéíóú',
                  'label'  => '<>&\'"áéíóú',
                  'scheme' => 'http://example.com/schema1'),
            array('term'   => 'cat_dog2',
                  'label'  => 'cat_dog2',
                  'scheme' => null)
        );
        $this->assertEquals($expected, (array)$entry->getCategories());
    }

}
