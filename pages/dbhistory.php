<?php
?>
<div class="inner_container">
    <div class="row ">
        <div class="columns ten">
            <?php $pageoptions = '
			<div class="pager">
			<a href="#" class="first"  title="Erste Seite" >Erste</a>
			<a href="#" class="prev"  title="Vorherige Seite" ><span class="genericon genericon-previous"></span>Vorherige Seite</a>
			<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
			<a href="#" class="next" title="Nächste Seite" >Nächste Seite<span class="genericon genericon-next"></span></a>
			<a href="#" class="last"  title="Letzte Seite" >Letzte</a>
			
			Seite: <select class="gotoPage"></select>
			Zeile pro Seite: <select class="pagesize">
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="300">300</option>
			</select>
			</div>';
            ?>
            <?php
            echo $pageoptions . $this->dbqueries->getContent() . $pageoptions; ?>
        </div>
    </div>
</div>