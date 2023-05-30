<div class="NearataDstsBBCode">
    <div class="title">{{ $translator->trans('nearata-dsts.forum.post.bbcode.hidden_content') }}</div>
    <div class="content">
        <xsl:if test="@bbcode_error = ''">
            {ANYTHING}
        </xsl:if>
        <xsl:if test="@bbcode_error != ''">
            {@bbcode_error}
        </xsl:if>
    </div>
</div>
