{extends file="layout.tpl"}

{block name=title}Bireysel Müşteriler{/block}

{block name=content}

<div class="crumbs">
	<ul id="breadcrumbs" class="breadcrumb">
		<li>
			<i class="icon-home"></i>
			<a href="/customer/individual">Müşteriler</a>
		</li>
		<li class="current">
			<a href="/customer/individual" title="">Müşteri Listesi</a>
		</li>
	</ul>
</div>

<div class="page-header">
	<div class="page-title">
		<h3>Müşteri Listesi</h3>
	</div>
	<div class="pull-right title_right_bar">
		<a href="javascript:void(0)" class="btn btn-inverse" id="filter_panel_toggle">Arama Kriterlerini Göster/Gizle</a>
	</div>
</div>

<div class="row" id="search_panel_div">
	<div class="col-md-12">
	
		<div class="widget box">
			<div class="widget-header">
				<h4><i class="icon-reorder"></i> Arama Kriterleri</h4>
			</div>
							
			<div class="widget-content" style="display: block;">
				
				<form class="form-horizontal row-border" action="/customer/individual" method="get" name="customer_individual_filter" id="customer_individual_filter">
				
					<input type="hidden" name="page" value="{$data.getdata.current_page|default:"1"}" />
					<input type="hidden" name="order" id="order_input" value="{$data.getdata.current_page|default:"created_on|desc"}" /> 
				
					<div class="row">
					<div class="col-md-6">
				
						<div class="form-group">
							<label class="col-md-4 control-label" for="title">Ad :</label>
							<div class="col-md-8"><input class="form-control" type="text" name="title" id="title" value="{$data.getdata.title|default:""}" /></div>
						</div>
						
					</div>
					
					<div class="col-md-6">
				
						<div class="form-group">
							<label class="col-md-4 control-label" for="created_on">Kayıt Tarihi:</label>
							<div class="col-md-8">
							
								<div class="date_range">
									<span class="btn btn-default">Tarih aralığı seç</span>
								</div>

								<input type="hidden" name="created_on_start" id="created_on_start" value="{$data.getdata.created_on_start|default:"01.01.2012"}" /> 
								<input type="hidden" name="created_on_end" id="created_on_end" value="{$data.getdata.created_on_end|default:{$smarty.now|date_format:"%d.%m.%Y"}}" />
								
							</div>
						</div>
						
					</div>
					

					
					<div class="col-md-6">
				
						<div class="form-group">
							<label class="col-md-4 control-label" for="identity_no">T.C. Kimlik No:</label>
							<div class="col-md-8"><input class="form-control" type="text" name="identity_no" id="identity_no" value="{$data.getdata.identity_no|default:""}" /></div>
						</div>
						
					</div>
					
					
					</div>
					
					<div class="row">
						<div class="col-md-12">
							<div class="form-actions">
								<a class="btn btn-danger pull-right" href="/customer/individual">Kriterleri Temizle</a>
								<button type="submit" class="btn btn-primary pull-right"><i class="icon-search"></i> Kriterleri Uygula</button>
							</div>
						</div>
						
					</div>
					
				</form>
				
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
			
		<div class="widget box">
			<div class="widget-content" style="display: block;">
				
				{if $data.customer_list eq null}
				
					<div class="alert alert-danger">Aradığınız kriterlere uygun bireysel müşteri bulunamadı.</div>
					
				{else}
				
				<div class="table-footer">
				
					<div class="col-md-6">
					
						<p><i class="icon-lightbulb"></i> <span>Seçtiğiniz kriterlere göre <strong>{$data.customer_count|default:"0"}</strong> müşteri bulundu.</span></p>
					
					</div>
					
					<div class="col-md-6">
					
						<div class="table_order_filter">
							<label>Sıralama:</label>
							<select name="order" id="order_select">
								<option value="created_on|desc"{if $data.getdata.order eq "" OR $data.getdata.order eq "created_on|desc"} selected="selected"{/if}>Kayıt tarihine göre (Önce en yeni)</option>
								<option value="created_on|asc"{if $data.getdata.order eq "created_on|asc"} selected="selected"{/if}>Kayıt tarihine göre (Önce en eski)</option>
							
							</select>
						</div>
						
					</div>
				
				</div>
				
				<hr style="size:1px" />
				
				<table class="table table-hover table-responsive">
					<thead>
						<tr>
							<th></th>
                            <!--<th>Skor</th>-->
							<th>Müşteri ID#</th>
							<th>T.C. Kimlik No</th>
							<th>Ad Soyad</th>
							<th>Kayıt Tarihi</th>
						</tr>
					</thead>
					<tbody>
					{foreach $data.customer_list AS $item}
						<tr>
							<td>
								{if {yetki x="customer_detail"} eq true OR {yetki x="customer_detail"} eq true}
								<div class="btn-group">
									<button class="btn btn-sm dropdown-toggle btn-default" data-toggle="dropdown">
										<i class="icon-cog"></i>
										<span class="caret"></span>
									</button>
									
									<ul class="dropdown-menu">
									{if {yetki x="customer_detail"} eq true}
										<li><a href="/customer/detail/{$item.customer_id}"><i class="icon-list-alt"></i> Müşteri Detayları</a></li>
									{/if}
									</ul>
								</div>
								{else}
									-
								{/if}
							</td>
                            <!--<td><button class="btn btn-sm btn-success btn bs-tooltip" data-placement="bottom" data-original-title="372">
                                        A</button></td>-->
							<td><a href="/customer/detail/{$item.customer_id}">{$item.customer_id}</a></td>
							<td>{$item.identity_no}</td>
							<td><a href="/customer/detail/{$item.customer_id}">{$item.name} {$item.middle_name} {$item.surname}</a></td>
							
							<td>{$item.created_on|date_format:"%d.%m.%Y - %H:%M:%S"}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
				
				{if $data.customer_count gt $data.page_limit}
				
				<hr style="size:1px" />
				
				<div class="table-footer">
				
					<div class="col-md-4">
						
						
					</div>
					
					<div class="col-md-8">
						
						<ul class="pagination">
							{for $i=1 to $data.total_page}
							<li{if $i eq $data.current_page} class="active"{/if}><a href="/customer/individual{$data.page_link}&page={$i}">{$i}</a></li>
							{/for}
						</ul>
						
						
					</div>
				
				</div>
				
				{/if}
				
				{/if}
				
			</div>
		</div>
	</div>
</div>

{/block}