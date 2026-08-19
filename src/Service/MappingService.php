<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Service;

use SOWP\QuickMultilingual\Config;

/**
 * Reads and resolves the so_qmp_page_mapping_N option rows.
 */
final readonly class MappingService {

    /**
     * Find the mapping row that contains the given page id (on either side).
     *
     * @return array{primary?: int, secondary?: int}|null
     */
    public function row_for_page( int $page_id ): ?array {
        foreach ( $this->all_rows() as $row ) {
            if ( isset( $row['primary'] ) && absint( $row['primary'] ) === $page_id ) {
                return $row;
            }
            if ( isset( $row['secondary'] ) && absint( $row['secondary'] ) === $page_id ) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Return the counterpart page id for the given page, or null if unmatched.
     */
    public function counterpart( int $page_id ): ?int {
        $row = $this->row_for_page( $page_id );
        if ( null === $row ) {
            return null;
        }
        if ( isset( $row['primary'] ) && absint( $row['primary'] ) === $page_id ) {
            return isset( $row['secondary'] ) ? absint( $row['secondary'] ) : null;
        }
        return isset( $row['primary'] ) ? absint( $row['primary'] ) : null;
    }

    /**
     * First mapped secondary page id (the secondary "homepage" the folder redirects to).
     */
    public function first_secondary(): ?int {
        $row = get_option( Config::option_key( 1 ) );
        if ( is_array( $row ) && ! empty( $row['secondary'] ) ) {
            return absint( $row['secondary'] );
        }
        return null;
    }

    /**
     * @return list<array{primary?: int, secondary?: int}>
     */
    public function all_rows(): array {
        $rows = [];
        for ( $i = 1; $i <= Config::MAX_PAGE_MAPPINGS; $i++ ) {
            $row = get_option( Config::option_key( $i ) );
            if ( is_array( $row ) ) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}
