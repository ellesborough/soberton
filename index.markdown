---
# Feel free to add content and custom Front Matter to this file.
# To modify the layout, see https://jekyllrb.com/docs/themes/#overriding-theme-defaults

layout: home
---
<h1>Notable People</h1>
{%- assign promoted = site.people | where: "promote", true -%}
{%- for person in promoted -%}
<h2><a href="{{site.baseurl}}{{person.url}}">{{person.title}}</a></h2>
{{person.content}}    
{%- endfor -%}
