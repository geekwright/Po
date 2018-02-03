<h1>{_ msgid="This is how the story goes."}</h1>
{for $foo=6 to 0 step -1}
<p>{_ msgid="%d pig went to the market" msgid_plural="%d pigs went to the market" num=$foo}</p>
{/for}
