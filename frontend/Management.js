import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Image, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

const initial = { name: '', category: 'Gà tươi', description: '', price: '', wholesale_price: '', unit: 'kg', image: '', badge: '', is_active: true };
const roles = ['user', 'manager', 'admin'];
const labels = { user: 'User', manager: 'Manager', admin: 'Admin' };

export default function Management({ apiBase, token, user, onProductChange, notify }) {
  const [tab, setTab] = useState('products');
  const [products, setProducts] = useState([]);
  const [users, setUsers] = useState([]);
  const [form, setForm] = useState(initial);
  const [editing, setEditing] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [saving, setSaving] = useState(false);

  async function request(path, options = {}) {
    const response = await fetch(`${apiBase}/management${path}`, { ...options, headers: { Accept: 'application/json', 'Content-Type': 'application/json', Authorization: `Bearer ${token}` } });
    const data = await response.json();
    if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Không thể thực hiện yêu cầu.');
    return data;
  }
  async function load() {
    setLoading(true); setError('');
    try { if (tab === 'users') setUsers(await request('/users')); else setProducts(await request('/products')); }
    catch (err) { setError(err.message || 'Không tải được dữ liệu.'); } finally { setLoading(false); }
  }
  useEffect(() => { load(); }, [tab, token]);
  async function saveProduct() {
    if (form.price.trim() === '' || form.wholesale_price.trim() === '') { notify('Vui lòng nhập giá bán và giá sỉ.'); return; }
    setSaving(true);
    try {
      await request(editing ? `/products/${editing}` : '/products', { method: editing ? 'PUT' : 'POST', body: JSON.stringify({ ...form, price: Number(form.price), wholesale_price: Number(form.wholesale_price), badge: form.badge || null }) });
      setEditing(null); setForm(initial); await load(); onProductChange(); notify('Đã lưu sản phẩm.');
    } catch (err) { setError(err.message || 'Không lưu được sản phẩm.'); } finally { setSaving(false); }
  }
  async function saveUser(item, changes) {
    setSaving(true);
    try { await request(`/users/${item.id}`, { method: 'PUT', body: JSON.stringify({ role: item.role, is_active: item.is_active, ...changes }) }); await load(); notify('Đã cập nhật tài khoản.'); }
    catch (err) { setError(err.message || 'Không cập nhật được tài khoản.'); } finally { setSaving(false); }
  }
  function editProduct(item) { setEditing(item.id); setForm({ name: item.name, category: item.category, description: item.description, price: String(item.price), wholesale_price: String(item.wholesale_price), unit: item.unit, image: item.image, badge: item.badge || '', is_active: item.is_active }); }

  return <View style={styles.root}>
    <View style={styles.tabs}><Pressable onPress={() => setTab('products')} style={[styles.tab, tab === 'products' && styles.selected]}><Text style={styles.tabText}>Sản phẩm</Text></Pressable>{user.role === 'admin' ? <Pressable onPress={() => setTab('users')} style={[styles.tab, tab === 'users' && styles.selected]}><Text style={styles.tabText}>Tài khoản</Text></Pressable> : null}</View>
    <ScrollView keyboardShouldPersistTaps="handled">
      {tab === 'products' ? <View style={styles.panel}><Text style={styles.heading}>{editing ? 'Sửa sản phẩm' : 'Thêm sản phẩm'}</Text>
        {[['name','Tên sản phẩm'],['category','Danh mục'],['description','Mô tả'],['price','Giá bán (đ)'],['wholesale_price','Giá sỉ (đ)'],['unit','Đơn vị'],['image','Đường dẫn ảnh'],['badge','Nhãn']].map(([key, label]) => <View key={key} style={styles.field}><Text style={styles.label}>{label}{key === 'badge' ? '' : ' *'}</Text><TextInput value={form[key]} onChangeText={value => setForm({ ...form, [key]: value })} keyboardType={key.includes('price') ? 'numeric' : 'default'} multiline={key === 'description'} style={styles.input} /></View>)}
        <Pressable onPress={() => setForm({ ...form, is_active: !form.is_active })} style={styles.toggle}><Text style={styles.text}>{form.is_active ? '☑ Đang bán' : '☐ Ngừng bán'}</Text></Pressable><View style={styles.actions}><Action text={saving ? 'Đang lưu…' : 'Lưu sản phẩm'} onPress={saveProduct} disabled={saving} />{editing ? <Action text="Hủy sửa" onPress={() => { setEditing(null); setForm(initial); }} secondary /> : null}</View>
        <Text style={styles.heading}>Danh sách sản phẩm</Text>{loading ? <ActivityIndicator color="#183d2b" /> : null}{error ? <Text style={styles.error}>{error}</Text> : null}{products.map(item => <View key={item.id} style={styles.row}><Image source={{ uri: item.image }} style={styles.image} /><View style={styles.grow}><Text style={styles.name}>{item.name}</Text><Text style={styles.text}>{new Intl.NumberFormat('vi-VN').format(item.price)}đ / {item.unit} · {item.is_active ? 'Đang bán' : 'Ngừng bán'}</Text></View><Action text="Sửa" onPress={() => editProduct(item)} secondary /></View>)}
      </View> : <View style={styles.panel}><Text style={styles.heading}>Quản lý tài khoản</Text><Text style={styles.text}>Chọn vai trò và trạng thái. Bạn không thể tự khóa hoặc hạ quyền tài khoản đang dùng.</Text>{loading ? <ActivityIndicator color="#183d2b" /> : null}{error ? <Text style={styles.error}>{error}</Text> : null}{users.map(item => <View key={item.id} style={styles.userCard}><Text style={styles.name}>{item.name}</Text><Text style={styles.text}>{item.email} · {item.is_active ? 'Đang hoạt động' : 'Đã khóa'}</Text><View style={styles.actions}>{roles.map(role => <Pressable key={role} disabled={saving || item.id === user.id} onPress={() => saveUser(item, { role })} style={[styles.role, item.role === role && styles.selected, (saving || item.id === user.id) && styles.disabled]}><Text style={styles.text}>{labels[role]}</Text></Pressable>)}</View><Action text={item.is_active ? 'Khóa tài khoản' : 'Mở khóa tài khoản'} disabled={saving || item.id === user.id} onPress={() => saveUser(item, { is_active: !item.is_active })} secondary /></View>)}</View>}
    </ScrollView>
  </View>;
}

function Action({ text, onPress, disabled, secondary }) { return <Pressable disabled={disabled} onPress={onPress} style={[styles.action, secondary && styles.secondary, disabled && styles.disabled]}><Text style={[styles.actionText, secondary && styles.secondaryText]}>{text}</Text></Pressable>; }
const styles = StyleSheet.create({ root: { flex: 1, minHeight: 350 }, tabs: { flexDirection: 'row', gap: 8, marginBottom: 14 }, tab: { padding: 11, borderWidth: 1, borderColor: '#d9dfd5' }, selected: { backgroundColor: '#e3eddf', borderColor: '#183d2b' }, tabText: { color: '#183d2b', fontSize: 14, fontWeight: '700' }, panel: { paddingBottom: 24 }, heading: { color: '#183d2b', fontSize: 18, fontWeight: '700', marginVertical: 14 }, field: { marginBottom: 10 }, label: { color: '#3a4b3e', fontSize: 13, fontWeight: '600', marginBottom: 5 }, input: { borderWidth: 1, borderColor: '#d9dfd5', padding: 10, color: '#202820', minHeight: 42, fontSize: 14 }, toggle: { paddingVertical: 10 }, text: { color: '#445449', fontSize: 13, lineHeight: 20 }, actions: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginVertical: 8 }, action: { backgroundColor: '#183d2b', paddingVertical: 10, paddingHorizontal: 13, alignSelf: 'flex-start', borderWidth: 1, borderColor: '#183d2b' }, actionText: { color: '#fff', fontSize: 13, fontWeight: '700' }, secondary: { backgroundColor: '#fff' }, secondaryText: { color: '#183d2b' }, disabled: { opacity: .45 }, row: { flexDirection: 'row', alignItems: 'center', gap: 10, borderTopWidth: 1, borderColor: '#e6e5db', paddingVertical: 12 }, image: { width: 56, height: 56 }, grow: { flex: 1 }, name: { color: '#183d2b', fontSize: 15, fontWeight: '700' }, userCard: { borderTopWidth: 1, borderColor: '#e6e5db', paddingVertical: 12, gap: 5 }, role: { borderWidth: 1, borderColor: '#d9dfd5', padding: 8 }, error: { color: '#aa2626', fontSize: 13 } });
