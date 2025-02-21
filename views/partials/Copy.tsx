import type { FC } from 'hono/jsx'
import { marked } from 'marked'

export type CopyProps = {
  body: string
}

export const Copy: FC<CopyProps> = ({ body }) => {
  const markdown = marked(body, { async: false })

  // biome-ignore lint/security/noDangerouslySetInnerHtml: No need for that.
  return <div className="copy" dangerouslySetInnerHTML={{ __html: markdown }} />
}
