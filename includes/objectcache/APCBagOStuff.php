<?php
/**
 * Object caching using PHP's APC accelerator.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Cache
 */

/**
 * This is a wrapper for APC's shared memory functions
 *
 * @ingroup Cache
 */
class APCBagOStuff extends BagOStuff {
	/**
	 * @param string $key
	 * @param int $casToken [optional]
	 * @return mixed
	 */
	public function get( $key, &$casToken = null ) {
		$val = apc_fetch( $key );

		$casToken = $val;

		if ( is_string( $val ) ) {
			if ( $this->isInteger( $val ) ) {
				$val = intval( $val );
			} else {
				$val = unserialize( $val );
			}
		}

		return $val;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int $exptime
	 * @return bool
	 */
	public function set( $key, $value, $exptime = 0 ) {
		if ( !$this->isInteger( $value ) ) {
			$value = serialize( $value );
		}

		apc_store( $key, $value, $exptime );

		return true;
	}

	/**
	 * @param mixed $casToken
	 * @param string $key
	 * @param mixed $value
	 * @param int $exptime
	 * @return bool
	 * @throws MWException
	 */
	protected function cas( $casToken, $key, $value, $exptime = 0 ) {
		// APC's CAS functions only work on integers
		throw new MWException( "CAS is not implemented in " . __CLASS__ );
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete( $key ) {
		apc_delete( $key );

		return true;
	}

	/**
	 * @param string $key
	 * @param Closure $callback Callback method to be executed
	 * @param int $exptime Either an interval in seconds or a unix timestamp for expiry
	 * @param int $attempts The amount of times to attempt a merge in case of failure
	 * @return bool Success
	 */
	public function merge( $key, Closure $callback, $exptime = 0, $attempts = 10 ) {
		return $this->mergeViaLock( $key, $callback, $exptime, $attempts );
	}

	public function incr( $key, $value = 1 ) {
		return apc_inc( $key, $value );
	}

	public function decr( $key, $value = 1 ) {
		return apc_dec( $key, $value );
	}
}
