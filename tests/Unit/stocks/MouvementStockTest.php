// tests/Unit/stocks/MouvementStockTest.php
namespace App\Tests\Unit\stocks;
use App\Entity\stocks\MouvementStock;
use PHPUnit\Framework\TestCase;

class MouvementStockTest extends TestCase {
public function testTypeMouvement() {
$mvt = new MouvementStock();
$mvt->setType("SORTIE"); // Comme dans ton erreur précédente
$mvt->setQuantite(20);
$this->assertEquals("SORTIE", $mvt->getType());
$this->assertEquals(20, $mvt->getQuantite());
}
}