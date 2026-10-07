<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Success pay");
?>

<main class="content return">
    <div class="content-images">
        <img class="image-1" alt="" src="/local/templates/luxol/images/img-1.png"> <img class="image-2" alt=""
            src="/local/templates/luxol/images/img-2.png"> <img class="image-3" alt=""
            src="/local/templates/luxol/images/img-3.png"> <img class="image-4" alt=""
            src="/local/templates/luxol/images/img-4.png">
    </div>
    <div class="breadcrumb">
        <div class="container">
            <?$APPLICATION->IncludeComponent(
            	"bitrix:breadcrumb",
            	"bread",
            Array()
            );?>
        </div>
    </div>
    <div class="about">
        <div class="container">
            <div class="section-header">
                <h1 class="section-header__title">Vaša platba prebehla úspešne.</h1>
            </div>
            <p>
                Vašu objednávku vybavíme v krátkom čase.
            </p>
            <p>O všetkom Vás budeme informovať.</p>
        </div>
    </div>
</main>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>