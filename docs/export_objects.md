# Export Data Objects

Data Objects haben viele unterschiedliche Attribute, deren Werte unbedingt mit exportiert werden müssen.

Das beginnt bei einfachen Textwerten, deren Inhalt mit dem Aufruf eines einfachen Getters möglich ist und endet bei 
`m:n` - Relationen zwischen Objekten, die dann eigentlich autoamtisch mit exportiert werden sollten, um eine gewisse
Konsistenz der Daten zu ermöglichen.

Zudem ändert sich das Pimcore System von Zeit zu Zeit, so dass Anpassungen in den Export der Attribute unvermeidlich sind.

Deshalb braucht es eine gute Architektur, die Verantwortlichkeiten sauber trennt und Änderungen einfach ermöglicht.

Wir nutzen im Export das Converter-and-Populator Pattern, um Pimcore Artefakte erstmal in ein DTo-Format zu konvertieren,
aus dem dann das gewünschte JSON Export Format wird.

Demzufolge definieren wir das Export Format quasi über die DTO-Klassen, die sich im Namespace 
`Neusta\Pimcore\ImportExportBundle\Model\Object` befinden.

```php
class DataObject extends Element
{
    public string $className;
    
    public bool $published = false;
    
    /** @var array<string, mixed> */
    public array $fields = []; // flexible set of property values
    
    /** @var array<string, array<string, mixed>> */
    public array $localizedFields = []; // flexible set of localized property values
    
    /** @var array<string, mixed> */
    public array $relations = []; // flexible set of relations (assets, objects, documents)
}
```
