<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	exclude-result-prefixes="php">
	<!--
	TODO
	method="html" не работает XMLHttpRequest.responseXML
	method="xml" не работает с пустым доктайпом
	-->
	<xsl:output method="html" encoding="UTF-8" indent="yes" />

	<xsl:param name="page"/>
	<xsl:param name="startTime"/>

	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html></xsl:text>
		<html>
			<head>
				<title><xsl:value-of select="menu/descendant::*[@url=$page]/@title" /></title>
				<xsl:call-template name="JavaScript"/>
				<xsl:call-template name="CSS" />
			</head>
			<body>

				<div id="Page">

					<xsl:call-template name="Header"/>

					<xsl:call-template name="Main"/>

					<div id="keeper"></div>
				</div>

				<xsl:call-template name="Footer"/>

			</body>
			</html>
	</xsl:template>

	<xsl:template name="JavaScript" />

	<xsl:template name="CSS" />

	<xsl:template name="Header">
		<header></header>
	</xsl:template>

	<xsl:template name="Main">
	
		<div id="Content">
			<xsl:call-template name="Content"/>
		</div>

	</xsl:template>

	<xsl:template name="Footer">
		<footer>
			<div class="mainfooter"></div>
			<div class="copyright">Copyright &#169; 2009 Казаков Роман Владимирович</div>
			<div class="designed">Designed by RC21</div>
		</footer>
	</xsl:template>

	<xsl:template name="Content"/>
</xsl:stylesheet>
