<?php
namespace nochso\WriteMe\Test\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\Frontmatter;
use nochso\WriteMe\Placeholder\PlaceholderCollection;
use nochso\WriteMe\Placeholder\TOC;

class PlaceholderCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $list = [new TOC(), new Frontmatter()];
        $collection = new PlaceholderCollection($list);
        $this->assertSame($list, $collection->toArray());
    }

    public function testAdd()
    {
        $placeholder = new TOC();
        $collection = new PlaceholderCollection();
        $collection->add($placeholder);
        $this->assertSame([$placeholder], $collection->toArray());
    }

    public function testAddMany()
    {
        $list = [new TOC(), new Frontmatter()];
        $collection = new PlaceholderCollection();
        $collection->addMany($list);
        $this->assertSame($list, $collection->toArray());
    }

    public function testGetPriorities()
    {
        $collection = new PlaceholderCollection();
        $collection->add(new TOC());
        $this->assertSame([Placeholder::PRIORITY_LAST], $collection->getPriorities());

        $collection->add(new Frontmatter());
        $this->assertSame([Placeholder::PRIORITY_FIRST, Placeholder::PRIORITY_LAST], $collection->getPriorities(), 'Priorities must be sorted low to high');
    }

    public function testGetPlaceholdersForCall_WhenNoPlaceholdersFound_MustReturnEmptyArray()
    {
        $collection = new PlaceholderCollection();
        $call = new Call();
        $this->assertSame([], $collection->getPlaceholdersForCall($call)->toArray());
    }

    public function testGetPlaceholdersForCall_SpecificPlaceholdersHavePriorityOverPotentialFrontmatter()
    {
        // Frontmatter must not accidentally replace a @toc@ call even though it *theoretically* would be frontmatter.
        $collection = new PlaceholderCollection([new TOC(), new Frontmatter()]);
        $document = new Document("---\ntoc: foo\n---\n@toc@");
        $call = Call::extractFirstCall($document, Placeholder::PRIORITY_FIRST);
        $placeholders = $collection->getPlaceholdersForCall($call)->toArray();
        $this->assertCount(0, $placeholders);

        $tocCall = Call::extractFirstCall($document, Placeholder::PRIORITY_LAST);
        $placeholders = $collection->getPlaceholdersForCall($tocCall)->toArray();
        $this->assertCount(1, $placeholders);
        $this->assertSame(TOC::class, get_class($placeholders[0]));
    }
}
