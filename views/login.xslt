<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="includes/banshee.xslt" />

<xsl:template match="content">
<h1>Login</h1>
<table><tr><td class="login">
<form action="{url}" method="post" onSubmit="javascript:hash_password(); return true;">
<table>
<tr><td>Username:</td><td><input type="text" name="username" id="username" class="text" /></td></tr>
<tr><td>Password:</td><td><input type="password" name="password" id="password" class="text" /></td></tr>
</table>
<p>Bind session to IP (<font style="font-size:10px"><xsl:value-of select="remote_addr" /></font>): <input type="checkbox" name="bind_ip">
<xsl:if test="bind">
<xsl:attribute name="checked">checked</xsl:attribute>
</xsl:if>
</input></p>
<xsl:call-template name="show_messages" />
<p>
<input type="submit" value="Login" class="button" />
</p>

<input type="hidden" id="use_cr_method" name="use_cr_method" value="no" />
</form>
<input type="hidden" id="challenge" value="{challenge}" />
</td></tr></table>

<script type="text/javascript" src="/js/md5.js" />
<script type="text/javascript" src="/js/login.js" />
</xsl:template>

</xsl:stylesheet>
