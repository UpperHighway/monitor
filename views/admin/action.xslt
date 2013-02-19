<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="../includes/banshee.xslt" />
<xsl:include href="../includes/pagination.xslt" />

<!--
//
//  Log template
//
//-->
<xsl:template match="log">
<table class="list">
<tr><th class="timestamp">Timestamp</th><th class="ip_address">IP address</th><th class="user">User</th><th class="event">Event</th></tr>
<xsl:for-each select="list/entry">
<tr>
<td><xsl:value-of select="timestamp" /></td>
<td><xsl:value-of select="ip" /></td>
<td><xsl:value-of select="username" /></td>
<td><xsl:value-of select="event" /></td>
</tr>
</xsl:for-each>
</table>
<xsl:apply-templates select="pagination" />

<input type="button" value="Back" class="button" onClick="javascript:document.location='/admin'" />
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1><img src="/images/icons/action.png" class="title_icon" />Action log</h1>
<xsl:apply-templates select="log" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
