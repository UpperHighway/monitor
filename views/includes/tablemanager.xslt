<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="alphabetize.xslt" />
<xsl:include href="pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<div class="{@class}">
<table class="{@class}">
<thead>
<tr>
<xsl:for-each select="labels/label">
	<th class="{@name}"><a href="?order={@name}"><xsl:value-of select="." /></a></th>
</xsl:for-each>
</tr>
</thead>

<tbody>
<xsl:for-each select="items/item">
<tr class="click" onClick="javascript:document.location='/{/output/page}/{@id}'">
<xsl:for-each select="value">
	<td><xsl:value-of select="." /></td>
</xsl:for-each>
</tr>
</xsl:for-each>
</tbody>
</table>
<xsl:apply-templates select="alphabetize" />
<xsl:apply-templates select="pagination" />
</div>

<xsl:if test="@allow_create='yes'">
<input type="button" value="New {labels/@name}" class="button" onClick="javascript:document.location='/{/output/page}/new'" />
</xsl:if>
<xsl:if test="../back">
<input type="button" value="Back" class="button" onClick="javascript:document.location='/{../back}'" />
</xsl:if>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post" enctype="multipart/form-data">
<xsl:if test="form/@id">
<input type="hidden" name="id" value="{form/@id}" />
</xsl:if>

<table class="tablemanager">
<xsl:for-each select="form/element">
<tr class="{@name}"><td><xsl:value-of select="label" />:</td><td>
<xsl:choose>
	<!-- Boolean -->
	<xsl:when test="@type='boolean'">
		<input type="checkbox" id="{@name}" name="{@name}"><xsl:if test="value='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>
	</xsl:when>
	<!-- Date and time -->
	<xsl:when test="@type='datetime'">
		<input type="text" id="{@name}" name="{@name}" value="{value}" readonly="readonly" class="text datetime" />
	</xsl:when>
	<!-- Enumerate -->
	<xsl:when test="@type='enum' or @type='foreignkey'">
		<select id="{@name}" name="{@name}" class="text">
		<xsl:for-each select="options/option">
		<option value="{@value}">
			<xsl:if test="@value=../../value"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of select="." />
		</option>
		</xsl:for-each>
		</select>
	</xsl:when>
	<!-- Text -->
	<xsl:when test="@type='text' or @type='ckeditor'">
		<textarea id="{@name}" name="{@name}" class="text">
			<xsl:if test="@type='ckeditor'">
				<xsl:attribute name="id">editor</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="value" />
		</textarea>
	</xsl:when>
	<!-- Blob -->
	<xsl:when test="@type='blob'">
		<input type="file" id="{@name}" name="{@name}" />
	</xsl:when>
	<!-- Other -->
	<xsl:otherwise>
		<input type="text" id="{@name}" name="{@name}" value="{value}" class="text" />
	</xsl:otherwise>
</xsl:choose>
</td></tr>
</xsl:for-each>
</table>

<xsl:for-each select="form/element[@type='datetime']">
<script type="text/javascript">
&lt;!--
	Calendar.setup({
		inputField: "<xsl:value-of select="@name" />",
		button    : "<xsl:value-of select="@name" />",
		ifFormat  : "%Y-%m-%d %H:%M:%S",
		showsTime : true,
		timeFormat: "24",
		firstDay  : 1
	});
//-->
</script>
</xsl:for-each>

<input type="submit" name="submit_button" value="Save {form/@name}" class="button" />
<input type="button" value="Cancel" class="button" onClick="javascript:document.location='/{/output/page}'" />
<xsl:if test="form/@id and form/@allow_delete='yes'">
<input type="submit" name="submit_button" value="Delete {form/@name}" class="delete button" onClick="javascript:return confirm('DELETE: Are you sure?')" />
</xsl:if>
<xsl:if test="form/element[@type='ckeditor']">
<input type="button" value="Start CKEditor" id="start_cke" class="button" onClick="javascript:start_ckeditor(300)" />
</xsl:if>
</form>
</xsl:template>

<!--
//
//  Tablemanager template
//
//-->
<xsl:template match="tablemanager">
<xsl:if test="icon"><img src="/images/icons/{icon}" class="title_icon" /></xsl:if><h1><xsl:value-of select="name" /> administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
