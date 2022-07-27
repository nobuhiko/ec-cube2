# public.mtb_mail_template

## Description

メールテンプレート

## Columns

| Name | Type | Default | Nullable | Children | Parents | Comment |
| ---- | ---- | ------- | -------- | -------- | ------- | ------- |
| id | smallint |  | false |  |  | ID |
| name | text |  | true |  |  | 名称 |
| rank | smallint | 0 | false |  |  | 表示順 |

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| mtb_mail_template_pkey | PRIMARY KEY | PRIMARY KEY (id) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| mtb_mail_template_pkey | CREATE UNIQUE INDEX mtb_mail_template_pkey ON public.mtb_mail_template USING btree (id) |

## Relations

![er](public.mtb_mail_template.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)