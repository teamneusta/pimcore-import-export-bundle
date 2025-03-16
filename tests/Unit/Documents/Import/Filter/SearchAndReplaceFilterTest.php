<?php declare(strict_types=1);

namespace App\Tests\Unit\ImportExportBundle\Documents\Import\Filter;

use Neusta\Pimcore\ImportExportBundle\Documents\Import\Filter\SearchAndReplaceFilter;
use PHPUnit\Framework\TestCase;

class SearchAndReplaceFilterTest extends TestCase
{
    private SearchAndReplaceFilter $filter;

    public function testFilterAndReplace_regular_case(): void
    {
        $this->filter = new SearchAndReplaceFilter(
            [
                '%placeholder_1%' => 'value1',
                '%placeholder_2%' => 'value2',
            ]
        );

        $result = $this->filter->filterAndReplace('%placeholder_1% %placeholder_2%');

        self::assertEquals('value1 value2', $result);
    }

    public function testFilterAndReplace_correct_sequence_case(): void
    {
        $this->filter = new SearchAndReplaceFilter(
            [
                '%placeholder_1%' => '%placeholder_2%',
                '%placeholder_2%' => 'value2',
            ]
        );

        $result = $this->filter->filterAndReplace('%placeholder_1% %placeholder_1%');

        self::assertEquals('value2 value2', $result);
    }

    public function testFilterAndReplace_incorrect_sequence_case(): void
    {
        $this->filter = new SearchAndReplaceFilter(
            [
                '%placeholder_2%' => 'value2',
                '%placeholder_1%' => '%placeholder_2%',
            ]
        );

        $result = $this->filter->filterAndReplace('%placeholder_1% %placeholder_1%');

        self::assertEquals('%placeholder_2% %placeholder_2%', $result);
    }
}
