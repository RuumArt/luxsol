<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Na stiahnutie");
$APPLICATION->SetPageProperty("description", "Dokumenty na stiahnutie: vzorový formulár na odstúpenie od zmluvy a ďalšie dokumenty Luxsol.sk.");
$APPLICATION->SetTitle("Na stiahnutie");
?><main class="content return">
<div class="content-images">
 <img src="/local/templates/luxol/images/img-1.png" alt="" class="image-1"> <img src="/local/templates/luxol/images/img-2.png" alt="" class="image-2"> <img src="/local/templates/luxol/images/img-3.png" alt="" class="image-3"> <img src="/local/templates/luxol/images/img-4.png" alt="" class="image-4">
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
<div class="return-block">
	<div class="container">
		<div class="section-header">
			<h1 class="section-header__title">Na stiahnutie</h1>
		</div>
		<div class="text-block downloads">
			<p>Dokumenty, ktoré si môžete stiahnuť, vytlačiť alebo vyplniť.</p>
			<ul>
				<li>
					<strong>Vzorový formulár na odstúpenie od zmluvy</strong>
					<span class="downloads__formats">
						<a href="/upload/dokumenty/formular-na-odstupenie-od-zmluvy.pdf" download>PDF na vytlačenie</a> (122 kB) &middot;
						<a href="/upload/dokumenty/formular-na-odstupenie-od-zmluvy.docx" download>DOCX na vyplnenie v počítači</a> (37 kB)
					</span>
					<span class="downloads__formats">Odstúpenie môžete poslať aj online cez formulár <a href="/odstupenie-od-zmluvy/">Odstúpiť od zmluvy</a>.</span>
				</li>
			</ul>
		</div>
	</div>
</div>
 </main><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>