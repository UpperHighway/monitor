<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="includes/banshee.xslt" />

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post" onSubmit="javascript:hash_passwords(); return true;">
<table>
<tr height="30" valign="top"><td>Current password:</td><td><input type="password" name="current" id="current" class="text" /></td></tr>
<tr><td>New password:</td><td><input type="password" name="password" id="password" class="text" /> <font style="font-size:10px">(will not be changed when left blank)</font></td></tr>
<tr><td>Repeat password:</td><td><input type="password" name="repeat" id="repeat" class="text" /></td></tr>
<tr><td>E-mail address:</td><td><input type="text" name="email" value="{email}" class="text" /></td></tr>
</table><br />

<input type="submit" name="submit_button" value="Update profile" class="button" />
<xsl:if test="cancel">
<input type="button" value="{cancel}" class="button" onClick="javascript:document.location='/{cancel/@page}'" />
</xsl:if>

<input type="hidden" id="password_hashed" name="password_hashed" value="no" />
</form>

<h2>Recent account activity</h2>
<table class="list">
<tr>
<th>IP address</th>
<th>Timestamp</th>
<th>Message</th>
</tr>
<xsl:for-each select="actionlog/log">
<tr>
<td><xsl:value-of select="ip" /></td>
<td><xsl:value-of select="timestamp" /></td>
<td><xsl:value-of select="message" /></td>
</tr>
</xsl:for-each>
</table>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>User profile</h1>
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
