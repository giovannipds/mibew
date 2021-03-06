<?php
/*
 * Copyright 2005-2013 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

function tpl_content($page) {
?>

<?php echo getlocal("page.analysis.userhistory.intro") ?>
<br />
<br />

<table class="list">
<thead>
<tr class="header">
<th>
	<?php echo getlocal("page.analysis.search.head_name") ?>
</th><th>
	<?php echo getlocal("page.analysis.search.head_host") ?>
</th><th>
	<?php echo getlocal("page.analysis.search.head_operator") ?>
</th><th>
	<?php echo getlocal("page.analysis.search.head_time") ?>
</th></tr>
</thead>
<tbody>
<?php 
if( $page['pagination.items'] ) {
	foreach( $page['pagination.items'] as $chatthread ) { ?>
	<tr>
		<td>
			<a href="<?php echo MIBEW_WEB_ROOT ?>/operator/threadprocessor.php?threadid=<?php echo $chatthread->id ?>" target="_blank" onclick="this.newWindow = window.open('<?php echo MIBEW_WEB_ROOT ?>/operator/threadprocessor.php?threadid=<?php echo $chatthread->id ?>', '', 'toolbar=0,scrollbars=1,location=0,status=1,menubar=0,width=720,height=520,resizable=1');this.newWindow.focus();this.newWindow.opener=window;return false;"><?php echo to_page(htmlspecialchars($chatthread->userName)) ?></a>
		</td>
		<td>
			<?php echo get_user_addr(to_page($chatthread->remote)) ?>
		</td>
		<td>
			<?php if( $chatthread->agentName ) { ?><?php echo to_page(htmlspecialchars($chatthread->agentName)) ?><?php } ?>
		</td>
		<td>
			<?php echo date_diff_to_text($chatthread->modified-$chatthread->created) ?>, <?php echo date_to_text($chatthread->created) ?>
		</td>
	</tr>
<?php
	} 
} else {
?>
	<tr>
	<td colspan="5">
		<?php echo getlocal("tag.pagination.no_items") ?>
	</td>
	</tr>
<?php 
} 
?>
</tbody>
</table>
<?php
if ($page['pagination']) {
	echo "<br/>";
	echo generate_pagination($page['stylepath'], $page['pagination']);
}
?>

<?php 
} /* content */

require_once(dirname(__FILE__).'/inc_main.php');
?>