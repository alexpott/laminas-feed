<?php

/**
 * @see       https://github.com/laminas/laminas-feed for the canonical source repository
 * @copyright https://github.com/laminas/laminas-feed/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-feed/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Feed\Writer;

use Countable;
use DateTime;
use Iterator;
use Laminas\Feed\Writer\Renderer;

/**
* @category Laminas
* @package Laminas_Feed_Writer
*/
class Feed extends AbstractFeed implements Iterator, Countable
{

    /**
     * Contains all entry objects
     *
     * @var array
     */
    protected $entries = array();

    /**
     * A pointer for the iterator to keep track of the entries array
     *
     * @var int
     */
    protected $entriesKey = 0;

    /**
     * Creates a new Laminas_Feed_Writer_Entry data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     *
     * @return \Laminas\Feed\Writer\Entry
     */
    public function createEntry()
    {
        $entry = new Entry;
        if ($this->getEncoding()) {
            $entry->setEncoding($this->getEncoding());
        }
        $entry->setType($this->getType());
        return $entry;
    }

    /**
     * Appends a Laminas\Feed\Writer\Deleted object representing a new entry tombstone
     * to the feed data container's internal group of entries.
     *
     * @param Deleted $deleted
     * @return void
     */
    public function addTombstone(Deleted $deleted)
    {
        $this->entries[] = $deleted;
    }

    /**
     * Creates a new Laminas_Feed_Writer_Deleted data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     *
     * @return Deleted
     */
    public function createTombstone()
    {
        $deleted = new Deleted;
        if ($this->getEncoding()) {
            $deleted->setEncoding($this->getEncoding());
        }
        $deleted->setType($this->getType());
        return $deleted;
    }

    /**
     * Appends a Laminas\Feed\Writer\Entry object representing a new entry/item
     * the feed data container's internal group of entries.
     *
     * @param Entry $entry
     */
    public function addEntry(Entry $entry)
    {
        $this->entries[] = $entry;
    }

    /**
     * Removes a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param int $index
     * @throws Exception\InvalidArgumentException
     */
    public function removeEntry($index)
    {
        if (isset($this->entries[$index])) {
            unset($this->entries[$index]);
        }
        throw new Exception\InvalidArgumentException('Undefined index: ' . $index . '. Entry does not exist.');
    }

    /**
     * Retrieve a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param int $index
     * @throws Exception\InvalidArgumentException
     */
    public function getEntry($index = 0)
    {
        if (isset($this->entries[$index])) {
            return $this->entries[$index];
        }
        throw new Exception\InvalidArgumentException('Undefined index: ' . $index . '. Entry does not exist.');
    }

    /**
     * Orders all indexed entries by date, thus offering date ordered readable
     * content where a parser (or Homo Sapien) ignores the generic rule that
     * XML element order is irrelevant and has no intrinsic meaning.
     *
     * Using this method will alter the original indexation.
     *
     * @return void
     */
    public function orderByDate()
    {
        /**
         * Could do with some improvement for performance perhaps
         */
        $timestamp = time();
        $entries = array();
        foreach ($this->entries as $entry) {
            if ($entry->getDateModified()) {
                $timestamp = (int) $entry->getDateModified()->getTimestamp();
            } elseif ($entry->getDateCreated()) {
                $timestamp = (int) $entry->getDateCreated()->getTimestamp();
            }
            $entries[$timestamp] = $entry;
        }
        krsort($entries, \SORT_NUMERIC);
        $this->entries = array_values($entries);
    }

    /**
     * Get the number of feed entries.
     * Required by the Iterator interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->entries);
    }

    /**
     * Return the current entry
     *
     * @return Entry
     */
    public function current()
    {
        return $this->entries[$this->key()];
    }

    /**
     * Return the current feed key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->entriesKey;
    }

    /**
     * Move the feed pointer forward
     *
     * @return void
     */
    public function next()
    {
        ++$this->entriesKey;
    }

    /**
     * Reset the pointer in the feed object
     *
     * @return void
     */
    public function rewind()
    {
        $this->entriesKey = 0;
    }

    /**
     * Check to see if the iterator is still valid
     *
     * @return boolean
     */
    public function valid()
    {
        return 0 <= $this->entriesKey && $this->entriesKey < $this->count();
    }

    /**
     * Attempt to build and return the feed resulting from the data set
     *
     * @param  string  $type The feed type "rss" or "atom" to export as
     * @param  bool    $ignoreExceptions
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function export($type, $ignoreExceptions = false)
    {
        $this->setType(strtolower($type));
        $type = ucfirst($this->getType());
        if ($type !== 'Rss' && $type !== 'Atom') {
            throw new Exception\InvalidArgumentException('Invalid feed type specified: ' . $type . '.'
            . ' Should be one of "rss" or "atom".');
        }
        $renderClass = 'Laminas\\Feed\\Writer\\Renderer\\Feed\\' . $type;
        $renderer = new $renderClass($this);
        if ($ignoreExceptions) {
            $renderer->ignoreExceptions();
        }
        return $renderer->render()->saveXml();
    }

}
