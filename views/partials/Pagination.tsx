import type { FC } from 'hono/jsx'

export type PaginationProps = {
  prevLink?: string
  nextLink?: string
}

export const Pagination: FC<PaginationProps> = ({ prevLink, nextLink }) => {
  return (
    <nav class="pagination block">
      {prevLink && (
        <a rel="prev" href={prevLink}>
          &larr;
        </a>
      )}

      {nextLink && (
        <a rel="next" href={nextLink}>
          &rarr;
        </a>
      )}
    </nav>
  )
}
