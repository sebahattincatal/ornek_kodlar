{extends file="layout.tpl"}

{block name=title}Müşteri İnceleme :: {$data.customer.name} {$data.customer.middle_name} {$data.customer.surname} ({$data.customer.customer_id}){/block}

{block name=content}

<div class="crumbs">
	<ul id="breadcrumbs" class="breadcrumb">
		<li>
			<i class="icon-home"></i> <a href="/customer/individual">Bireysel Müşteriler</a>
		</li>
		<li class="current">
			<a href="/customer/detail/{$data.customer.customer_id}" title="">{$data.customer.fullname}</a>
		</li>
	</ul>
</div>

<div class="page-header">
	<div class="page-title">
		<h3>{$data.customer.name} {$data.customer.middle_name} {$data.customer.surname}</h3>
		<span>Müşteri İnceleme :: {$data.customer.customer_id}</span>
	</div>
	<ul class="page-stats">
		<li>
			<div class="summary">
				<span>Bakiye</span>
				<h3>{$data.customer.bills.price|default:"-"}</h3>
			</div>
		</li>
		<li>
			<div class="summary">
				<span>Ödenmiş Taksit</span>
				<h3>{count($data.customer.bills.payments)|default:"-"}</h3>
			</div>
		</li>
		<li>
			<div class="summary">
				<span>Toplam Taksit</span>
				<h3>{$data.customer.bills.month_number|default:"-"}</h3>
			</div>
		</li>
	</ul>
</div>

<div class="row">
	<div class="col-md-12">
		
		
		
		<div class="tabbable tabbable-custom tabbable-full-width">
			<ul class="nav nav-tabs">
				<li{if $data.slot eq "summary"} class="active"{/if}><a href="/customer/detail/{$data.customer.customer_id}?slot=summary">Genel Bakış</a></li>
				<!--<li{if $data.slot eq "info"} class="active"{/if}><a href="/customer/detail/{$data.customer.customer_id}?slot=info">Rapor Bilgileri</a></li>-->
			</ul>
			<div class="tab-content row">
				
				{if $data.slot eq "summary"}
				
					{include file="customer/detail_summary_individual.tpl" data=$data}
				
				{elseif $data.slot eq "info"}
				
					{include file="customer/detail_info_individual.tpl" data=$data}
				
				
				{else}
					<div class="alert alert-danger">
						Teknik bir problem meydana geldi. Lütfen menüden bölümü seçerek tekrar deneyiniz.
					</div>
				{/if}
			
			</div>
		</div>
		
		
		
	</div>
</div>

{/block}